<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShiftRequest;
use App\Http\Requests\UpdateShiftRequest;
use App\Models\Shift;
use Inertia\Inertia;

class ShiftController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! auth()->user()->isAdmin()) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index()
    {
        $shifts = Shift::withCount('employees')->latest()->paginate(15);

        return Inertia::render('Shifts/Index', [
            'shifts' => $shifts,
        ]);
    }

    public function create()
    {
        return Inertia::render('Shifts/Create');
    }

    public function store(StoreShiftRequest $request)
    {
        Shift::create($request->validated());

        return redirect()->route('shifts.index')
            ->with('success', 'Turno criado com sucesso.');
    }

    public function show(Shift $shift)
    {
        $shift->load('employees');

        return Inertia::render('Shifts/Show', [
            'shift' => $shift,
        ]);
    }

    public function edit(Shift $shift)
    {
        return Inertia::render('Shifts/Edit', [
            'shift' => $shift,
        ]);
    }

    public function update(UpdateShiftRequest $request, Shift $shift)
    {
        $shift->update($request->validated());

        return redirect()->route('shifts.index')
            ->with('success', 'Turno atualizado com sucesso.');
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();

        return redirect()->route('shifts.index')
            ->with('success', 'Turno removido com sucesso.');
    }
}
