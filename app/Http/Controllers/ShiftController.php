<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShiftRequest;
use App\Http\Requests\UpdateShiftRequest;
use App\Models\Shift;
use Inertia\Inertia;

class ShiftController extends Controller
{
    // Apenas admin pode acessar turnos
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! auth()->user()->isAdmin()) {
                abort(403);
            }

            return $next($request);
        });
    }

    // Lista todos os turnos
    public function index()
    {
        $shifts = Shift::withCount('employees')->latest()->paginate(15);

        return Inertia::render('Shifts/Index', [
            'shifts' => $shifts,
        ]);
    }

    // Formulário para criar turno
    public function create()
    {
        return Inertia::render('Shifts/Create');
    }

    // Cria novo turno
    public function store(StoreShiftRequest $request)
    {
        Shift::create($request->validated());

        return redirect()->route('shifts.index')
            ->with('success', 'Turno criado com sucesso.');
    }

    // Detalhes de um turno
    public function show(Shift $shift)
    {
        $shift->load('employees');

        return Inertia::render('Shifts/Show', [
            'shift' => $shift,
        ]);
    }

    // Formulário para editar turno
    public function edit(Shift $shift)
    {
        return Inertia::render('Shifts/Edit', [
            'shift' => $shift,
        ]);
    }

    // Atualiza turno
    public function update(UpdateShiftRequest $request, Shift $shift)
    {
        $shift->update($request->validated());

        return redirect()->route('shifts.index')
            ->with('success', 'Turno atualizado com sucesso.');
    }

    // Remove turno
    public function destroy(Shift $shift)
    {
        $shift->delete();

        return redirect()->route('shifts.index')
            ->with('success', 'Turno removido com sucesso.');
    }
}
