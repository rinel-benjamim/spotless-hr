<?php

use App\AttendanceType;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('all attendance pdf templates render correct type labels', function () {
    // Create shift and employee
    $shift = Shift::factory()->create([
        'name' => 'Turno Manhã',
        'start_time' => '08:00',
        'end_time' => '17:00'
    ]);
    
    $employee = Employee::factory()->create([
        'shift_id' => $shift->id,
        'full_name' => 'João Silva',
        'employee_code' => 'EMP001'
    ]);
    
    // Create multiple attendances with different types
    $attendances = collect([
        Attendance::factory()->create([
            'employee_id' => $employee->id,
            'type' => AttendanceType::CheckIn,
            'recorded_at' => now()->setTime(8, 0),
            'notes' => 'Entrada manhã'
        ]),
        Attendance::factory()->create([
            'employee_id' => $employee->id,
            'type' => AttendanceType::CheckOut,
            'recorded_at' => now()->setTime(12, 0),
            'notes' => 'Saída almoço'
        ]),
        Attendance::factory()->create([
            'employee_id' => $employee->id,
            'type' => AttendanceType::CheckIn,
            'recorded_at' => now()->setTime(13, 0),
            'notes' => 'Entrada almoço'
        ]),
        Attendance::factory()->create([
            'employee_id' => $employee->id,
            'type' => AttendanceType::CheckOut,
            'recorded_at' => now()->setTime(17, 0),
            'notes' => 'Saída final'
        ])
    ]);
    
    $request = new \Illuminate\Http\Request([
        'employee_id' => $employee->id,
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d')
    ]);
    
    // Test attendances.blade.php template
    $attendances = Attendance::where('employee_id', $employee->id)->with('employee')->get();
    $html1 = view('pdf.attendances', compact('attendances', 'employee', 'request'))->render();
    
    expect($html1)->toContain('Entrada');
    expect($html1)->toContain('Saída');
    
    // Verify both types are present
    expect($html1)->toContain('badge-in');
    expect($html1)->toContain('badge-out');
    
    // Test employee-report.blade.php template
    $summary = [
        'days_worked' => 1,
        'absence_count' => 0,
        'late_count' => 0,
        'total_hours' => 8.0
    ];
    $monthName = now()->format('F Y');
    
    $html2 = view('pdf.employee-report', [
        'employee' => $employee,
        'attendances' => $attendances,
        'summary' => $summary,
        'monthName' => $monthName
    ])->render();
    
    expect($html2)->toContain('Entrada');
    expect($html2)->toContain('Saída');
    
    // Verify that both types appear in the attendance records
    expect($html2)->toContain('Entrada');
    expect($html2)->toContain('Saída');
    
    // Test employee-calendar.blade.php template
    $year = now()->year;
    $month = now()->month;
    $monthName = now()->format('F Y');
    
    // Group attendances by date for calendar
    $attendancesByDate = $attendances->groupBy(function ($attendance) {
        return $attendance->recorded_at->toDateString();
    });
    
    $absences = collect(); // No absences for this test
    
    $html3 = view('pdf.employee-calendar', [
        'employee' => $employee,
        'attendances' => $attendancesByDate,
        'absences' => $absences,
        'year' => $year,
        'month' => $month,
        'monthName' => $monthName
    ])->render();
    
    expect($html3)->toContain('ENT');
    expect($html3)->toContain('SAÍ');
    
    // Verify both types are present
    expect($html3)->toContain('check-in');
    expect($html3)->toContain('check-out');
});

test('attendance pdf export endpoint works correctly', function () {
    // Create admin user
    $admin = User::factory()->create(['role' => 'admin']);
    
    // Create shift and employee
    $shift = Shift::factory()->create();
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
    
    // Test PDF export endpoint
    $response = $this->actingAs($admin)->get(route('attendances.export-pdf', [
        'employee_id' => $employee->id,
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d')
    ]));
    
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
    
    // Test filename
    $contentDisposition = $response->headers->get('content-disposition');
    expect($contentDisposition)->toContain('relatorio-presencas-');
    expect($contentDisposition)->toContain('.pdf');
});