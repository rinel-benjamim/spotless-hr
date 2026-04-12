# Spotless HR - Guia Técnico e Explicativo

Este documento serve como guia introdutório para estudantes que desejam entender a estrutura técnica do sistema Spotless HR.

---

## 1. Conexão com a Base de Dados

### Onde fica a configuração?

A conexão com a base de dados está no ficheiro `.env` (raiz do projeto):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spotless_hr
DB_USERNAME=root
DB_PASSWORD=
```

### Como funciona?

O Laravel (framework usado neste projeto) usa **Eloquent ORM** para comunicar com a base de dados. Isso significa que não escrevemos SQL direto - usamos "Models" (modelos) em PHP.

**Exemplo de como buscar dados:**

```php
// Em vez de: SELECT * FROM employees WHERE active = 1
// Usa-se:
Employee::where('active', true)->get();
```

### Principais Models do Projeto

| Model           | Tabela           | Funcionalidade      |
| --------------- | ---------------- | ------------------- |
| `Employee`      | `employees`      | Funcionários        |
| `Attendance`    | `attendances`    | Registros de ponto  |
| `Schedule`      | `schedules`      | Escalas de trabalho |
| `Justification` | `justifications` | Faltas justificadas |
| `Absence`       | `absences`       | Registros de falta  |
| `Shift`         | `shifts`         | Turnos de trabalho  |

### Onde estão os Models?

Localização: `app/Models/`

Exemplo simples do Employee Model (`app/Models/Employee.php`):

```php
class Employee extends Model
{
    protected $table = 'employees';

    // Relacionamento: um funcionário tem muitos atendimentos
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // Relacionamento: um funcionário tem uma escala
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
```

---

## 2. Estrutura do Projeto

### Pastas Principais

```
spotless-hr/
├── app/
│   ├── Http/
│   │   ├── Controllers/  ← Controladores (lógica principal)
│   │   └── Middleware/    ← Verificações de segurança
│   ├── Models/           ← Modelos da base de dados
│   └── Services/         ← Lógica de negócio
├── resources/
│   ├── js/
│   │   ├── pages/        ← Páginas React (Frontend)
│   │   └── components/  ← Componentes reutilizáveis
│   └── views/
│       └── pdf/          ← Ficheiros para gerar PDFs
├── routes/
│   └── web.php           ← Definição de rotas
└── database/
    └── migrations/       ← Estrutura das tabelas
```

---

## 3. Códigos Importantes para Apresentação

### 3.1 DashboardController (`app/Http/Controllers/DashboardController.php`)

Este é o "cérebro" do dashboard. Contém os cálculos de métricas principais:

```php
// Cálculo de funcionários presentes hoje
public function presentToday()
{
    return Attendance::whereDate('check_in', today())
        ->whereNotNull('check_out')  // só conta se fez check-out
        ->count();
}

// Cálculo de ausências do mês
public function getAbsences($month, $year)
{
    $absences = 0;
    $startDate = Carbon::create($year, $month, 1)->startOfMonth();
    $endDate = $startDate->copy()->endOfMonth();

    // Verifica cada dia útil do mês
    for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
        if ($date->isWeekend()) continue;

        $employeesWithSchedule = Schedule::whereBetween('date', [
            $date->format('Y-m-d'),
            $date->format('Y-m-d')
        ])->count();

        $attendances = Attendance::whereDate('check_in', $date)->count();

        // Early exit conta como ausência
        $earlyExits = Attendance::whereDate('check_in', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('check_out')
                  ->orWhereRaw('TIME(check_out) < TIME(expected_check_out)');
            })->count();

        $absences += $employeesWithSchedule - $attendances + $earlyExits;
    }

    return $absences;
}
```

### 3.2 ScheduleController (`app/Http/Controllers/ScheduleController.php`)

Controla a visualização e criação de escalas:

```php
// Verificar se pode crear escalas (só Admin)
public function index()
{
    $canCreateSchedule = in_array(auth()->user()->role, ['Admin']);

    return Inertia::render('Schedules/Index', [
        'canCreateSchedule' => $canCreateSchedule,
    ]);
}
```

### 3.3 AttendanceService (`app/Services/AttendanceService.php`)

Lógica de negócio para ponto eletrónico:

```php
// Verificar se é saída antecipada
public function isEarlyExit(Attendance $attendance): bool
{
    if (!$attendance->check_out) return false;

    $expectedOut = $attendance->schedule->shift->end_time ?? '18:00:00';
    $actualOut = $attendance->check_out->format('H:i:s');

    return $actualOut < $expectedOut;
}
```

### 3.4 Middleware de Notificações

O ficheiro `app/Http/Middleware/SharePendingJustifications.php` mostraBadge de notificações:

```php
public function handle($request, Closure $next)
{
    // Conta justificações pendentes
    $pendingCount = Justification::where('status', 'pending')->count();

    Share::share('pendingJustificationsCount', $pendingCount);

    return $next($request);
}
```

---

## 4. Fluxo Básico do Sistema

### 4.1 Registro de Ponto

```
1. Employee faz Check-in → AttendanceController@checkIn
2. Sistema regista hora → Salva na tabela 'attendances'
3. Employee faz Check-out → AttendanceController@checkOut
4. sistema calcula horas lavoradas
```

### 4.2 Criação de Escala

```
1. Admin cria escala → ScheduleController@store
2. Sistema associa a employee e data → Salva na tabela 'schedules'
3. Outras páginas mostram a escala
```

### 4.3 Justificação de Falta

```
1. Employee pede justificativa → JustificationController@store
2. fica com status "pending" → waiting para aprovação
3. Admin aprova/rejeita → Altera status
4. Badge mostra número pendente
```

---

## 5. Conceitos Técnicos Importantes

### O que é Inertia?

Inertia é uma biblioteca que permite usar React dentro do Laravel sem criar API separada. O servidor gera a página e o JavaScript "pega" os dados.

### O que é Wayfinder?

Wayfinder gera automaticamente TypeScript a partir das rotas Laravel. Isso dá type-safe ao chamar rotas:

```typescript
// Em vez de '/attendances/check-in'
// Usa-se:
import { checkIn } from '@/actions/AttendanceController';
checkIn(); // { url: '/attendances/check-in', method: 'post' }
```

### O que é Pest?

Pest é o framework de testes usado neste projeto. Sintaxe mais simples que PHPUnit:

```php
it('calculates present today correctly', function () {
    $response = get('/dashboard');

    $response->assertSuccessful();
});
```

---

## 6. Comandos Úteis

### Iniciar o projeto

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run dev
```

### Executar testes

```bash
php artisan test
```

### Criar novos ficheiros

```bash
php artisan make:controller NomeController
php artisan make:model Nome -m  # Model + Migration
php artisan make:migration create_nome_table
```

---

## 7. Bits de Código Recomendados para Apresentação

Para um trabalho académico, estes são os pontos mais interessantes:

### Priority 1 - Funcionalidade Principal

- DashboardController: Métricas e cálculos
- AttendanceService: Lógica de check-in/out

### Priority 2 - Interface

- Schedules/Index.tsx: Visualização de escalas com.legendas coloridas
- Dashboard/\*: Diferentes dashboards para cada tipo de utilizador

### Priority 3 - Segurança

- Middleware de autenticação
- Políticas de autorização (quem pode fazer o quê)

### Priority 4 - Técnicas Avançadas

- Middleware compartilhado (pendingJustifications)
- Relationships do Eloquent
- Integração React + Laravel

---

## 8. Perguntas Frequentes

**P: Qual a diferença entre Model e Controller?**

R: Model = dados (representa a tabela). Controller = lógica (manipula os dados).

**P: Por que usar React com Laravel?**

R: Permite criar interfaces modernas e interativas mantendo a simplicidade do Laravel.

**P: O que acontece se alguém fazer check-in mas não fazer check-out?**

R: O sistema conta como "saída antecipada" e marca como ausência.

**P: Como o Admin aprova justificativas?**

R: Via página Justifications, com botões de Aprovar/Rejeitar que atualizam o status.

---

## 9. Recursos Adicionais

- Documentação Laravel: https://laravel.com/docs
- Documentação Inertia: https://inertiajs.com
- Documentação React: https://react.dev
- Tailwind CSS: https://tailwindcss.com

---

_Criado para fins educacionais - Sistema Spotless HR_
