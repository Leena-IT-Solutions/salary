<!DOCTYPE html>
<html>
<head>
    <title>Salary Slip</title>
</head>
<body>
    <p>Dear {{ $emp->employee->first_name }},</p>
    <p>Please find attached your salary slip for the month of <strong>{{ $payroll->payroll_name }}</strong>.</p>
    <p>Total Net Payable: <strong>Rs {{ $emp->net_payable_amount }}/-</strong></p>
    <br>
    <p>Best Regards,</p>
    <p>Payroll Department</p>
</body>
</html>
