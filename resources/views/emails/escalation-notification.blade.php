<!DOCTYPE html>
<html>
<head>
    <title>New Escalated Case</title>
</head>
<body>
<h1>New Escalated Case: #{{ $escalatedCase->id }}</h1>
<p>A new case has been escalated and requires your attention.</p>
<ul>
    <li>Case ID: {{ $escalatedCase->id }}</li>
    <li>Recipient ID: {{ $escalatedCase->recipient_id }}</li>
    <li>Escalation Level: {{ $escalatedCase->escalation_level }}</li>
    <li>Status: {{ $escalatedCase->status }}</li>
    <li>Created At: {{ $escalatedCase->created_at }}</li>
</ul>
<p>Please review and take appropriate action as soon as possible.</p>
</body>
</html>
