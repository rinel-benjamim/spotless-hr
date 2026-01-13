<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Models\Shift;
use Inertia\Inertia;

class EmployeeController extends Controller
{
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

    public function create()
    {
        $this->authorize('create', Employee::class);

        return Inertia::render('Employees/Create', [
            'shifts' => Shift::all(),
        ]);
    }

    public function store(StoreEmployeeRequest $request)
    {
        $data = $request->validated();

        if (isset($data['password'])) {
            $data['temp_password'] = $data['password'];
            unset($data['password']);
        }

        Employee::create($data);

        return redirect()->route('employees.index')
            ->with('success', 'Funcionário criado com sucesso.');
    }

    public function show(Employee $employee)
    {
        $this->authorize('view', $employee);

        $employee->load(['user', 'shift', 'attendances' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return Inertia::render('Employees/Show', [
            'employee' => $employee,
        ]);
    }

    public function edit(Employee $employee)
    {
        $this->authorize('update', $employee);

        return Inertia::render('Employees/Edit', [
            'employee' => $employee->load('shift'),
            'shifts' => Shift::all(),
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $employee->update($request->validated());

        return redirect()->route('employees.index')
            ->with('success', 'Funcionário atualizado com sucesso.');
    }

    public function destroy(Employee $employee)
    {
        $this->authorize('delete', $employee);

        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Funcionário removido com sucesso.');
    }
}
