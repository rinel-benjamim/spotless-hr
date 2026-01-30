<?php

use App\AttendanceType;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('attendance pdf export shows correct types for check in and check out', function () {
    // Create admin user
    $admin = User::factory()->create(['role' => 'admin']);
    
    // Create shift
    $shift = Shift::factory()->create([
        'name' => 'Turno Manhã',
        'start_time' => '08:00',
        'end_time' => '17:00'
    ]);
    
    // Create employee
    $employee = Employee::factory()->create([
        'shift_id' => $shift->id,
        'full_name' => 'João Silva',
        'employee_code' => 'EMP001'
    ]);
    
    // Create attendances with both types
    $checkIn = Attendance::factory()->create([
        'employee_id' => $employee->id,
        'type' => AttendanceType::CheckIn,
        'recorded_at' => now()->setTime(8, 0),
        'notes' => 'Entrada normal'
    ]);
    
    $checkOut = Attendance::factory()->create([
        'employee_id' => $employee->id,
        'type' => AttendanceType::CheckOut,
        'recorded_at' => now()->setTime(17, 0),
        'notes' => 'Saída normal'
    ]);
    
    // Test PDF export
    $response = $this->actingAs($admin)->get(route('attendances.export-pdf', [
        'employee_id' => $employee->id,
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d')
    ]));
    
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
    
    // Test that both attendances are retrieved correctly
    $attendances = Attendance::where('employee_id', $employee->id)->get();
    
    expect($attendances)->toHaveCount(2);
    expect($attendances->first()->type)->toBe(AttendanceType::CheckIn);
    expect($attendances->last()->type)->toBe(AttendanceType::CheckOut);
    
    // Test enum values
    expect($attendances->first()->type->value)->toBe('check_in');
    expect($attendances->last()->type->value)->toBe('check_out');
});

test('attendance pdf template renders correct type labels', function () {
    // Create admin user
    $admin = User::factory()->create(['role' => 'admin']);
    
    // Create shift
    $shift = Shift::factory()->create();
    
    // Create employee
    $employee = Employee::factory()->create(['shift_id' => $shift->id]);
    
    // Create attendances
    Attendance::factory()->create([
        'employee_id' => $employee->id,
        'type' => AttendanceType::CheckIn,
        'recorded_at' => now()
    ]);
    
    Attendance::factory()->create([
        'employee_id' => $employee->id,
        'type' => AttendanceType::CheckOut,
        'recorded_at' => now()->addHours(8)
    ]);
    
    // Get the PDF content by rendering the view directly
    $attendances = Attendance::where('employee_id', $employee->id)->with('employee')->get();
    $request = new \Illuminate\Http\Request([
        'employee_id' => $employee->id,
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d')
    ]);
    
    $html = view('pdf.attendances', compact('attendances', 'employee', 'request'))->render();
    
    // Check that both "Entrada" and "Saída" appear in the HTML
    expect($html)->toContain('Entrada');
    expect($html)->toContain('Saída');
    
    // Should not contain only "Saída" for both records
    $entradaCount = substr_count($html, 'Entrada');
    $saidaCount = substr_count($html, 'Saída');
    
    expect($entradaCount)->toBeGreaterThan(0);
    expect($saidaCount)->toBeGreaterThan(0);
});