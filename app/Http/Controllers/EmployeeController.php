<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Models\Shift;
use Inertia\Inertia;

/**
 * Classe responsável por EmployeeController.
 */
class EmployeeController extends Controller
{
    /**
     * Lista todos os funcionários.
     */
    public function index()
    {
        $this->authorize('viewAny', Employee::class);

        $employees = Employee::query()
            ->with(['user', 'shift'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Employees/Index', [
            'employees' => $employees,
        ]);
    }

    /**
     * Formulário para criar funcionário.
     */
    public function create()
    {
        $this->authorize('create', Employee::class);

        return Inertia::render('Employees/Create', [
            'shifts' => Shift::all(),
        ]);
    }

    /**
     * Cria novo funcionário.
     */
    public function store(StoreEmployeeRequest $request)
    {
        $data = $request->validated();

        $employee = new Employee($data);

        if (isset($data['password'])) {
            $employee->temp_password = $data['password'];
        }

        if (isset($data['email'])) {
            $employee->email = $data['email'];
        }

        $employee->save();

        return redirect()->route('employees.index')
            ->with('success', 'Funcionário criado com sucesso.');
    }

    /**
     * Detalhes de um funcionário.
     */
    public function show(Employee $employee)
    {
        $this->authorize('view', $employee);

        $employee->load(['user', 'shift', 'attendances' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return Inertia::render('Employees/Show', [
            'employee' => $employee,
            'canEdit' => auth()->user()->isAdmin(),
        ]);
    }

    /**
     * Formulário para editar funcionário.
     */
    public function edit(Employee $employee)
    {
        $this->authorize('update', $employee);

        return Inertia::render('Employees/Edit', [
            'employee' => $employee->load('shift'),
            'shifts' => Shift::all(),
        ]);
    }

    /**
     * Atualiza funcionário.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $data = $request->validated();

        // Sincroniza role se alterado
        if (isset($data['role']) && $employee->user) {
            $newRole = \App\EmployeeRole::from($data['role']);
            $employee->user->update(['role' => $newRole->getUserRole()]);
        }

        $employee->update($data);

        return redirect()->route('employees.index')
            ->with('success', 'Funcionário atualizado com sucesso.');
    }

    /**
     * Remove funcionário.
     */
    public function destroy(Employee $employee)
    {
        $this->authorize('delete', $employee);

        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Funcionário removido com sucesso.');
    }
}
