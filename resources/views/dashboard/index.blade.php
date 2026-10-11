@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
{{-- Demo data: replaced with real queries when the dashboard module is built. --}}
<div class="page-head">
    <div>
        <h1>Dashboard</h1>
        <p>Welcome back, {{ auth()->user()->name }}.</p>
    </div>
</div>

<h2 class="section-title">Queue</h2>
<div class="grid g-4">
    <div class="card stat green"><i class="icon-inbox"></i><div><div class="stat-label">Open tickets</div><div class="stat-value">48</div></div></div>
    <div class="card stat"><i class="icon-user-x"></i><div><div class="stat-label">Unassigned</div><div class="stat-value">7</div></div></div>
    <div class="card stat"><i class="icon-triangle-alert"></i><div><div class="stat-label">Critical</div><div class="stat-value">5</div></div></div>
    <div class="card stat"><i class="icon-circle-check"></i><div><div class="stat-label">Resolved, 30 days</div><div class="stat-value">126</div></div></div>
</div>

<h2 class="section-title">SLA</h2>
<div class="grid g-3">
    <div class="card stat red"><i class="icon-flame"></i><div><div class="stat-label">SLA breached</div><div class="stat-value">3</div></div></div>
    <div class="card stat amber"><i class="icon-hourglass"></i><div><div class="stat-label">Approaching breach</div><div class="stat-value">6</div></div></div>
    <div class="card stat"><i class="icon-gauge"></i><div><div class="stat-label">SLA met, 30 days</div><div class="stat-value">92%</div></div></div>
</div>

<h2 class="section-title">Needs attention</h2>
<div class="card table-wrap">
    <table class="table">
        <thead>
        <tr><th>Ticket</th><th>Priority</th><th>Agent</th><th>SLA</th><th class="right">Due</th></tr>
        </thead>
        <tbody>
        <tr>
            <td><span class="ticket-link">Payment gateway returns 500 on checkout</span><span class="ticket-id mono">TKT-2026-000118</span></td>
            <td><span class="priority critical">Critical</span></td>
            <td>Support Agent</td>
            <td><span class="badge red">Breached</span></td>
            <td class="right muted">35 min ago</td>
        </tr>
        <tr>
            <td><span class="ticket-link">Unable to export monthly invoices</span><span class="ticket-id mono">TKT-2026-000112</span></td>
            <td><span class="priority high">High</span></td>
            <td>Support Agent</td>
            <td><span class="badge red">Breached</span></td>
            <td class="right muted">2 h ago</td>
        </tr>
        <tr>
            <td><span class="ticket-link">Login OTP not received on mobile</span><span class="ticket-id mono">TKT-2026-000123</span></td>
            <td><span class="priority critical">Critical</span></td>
            <td>Support Agent</td>
            <td><span class="badge amber">Approaching</span></td>
            <td class="right muted">in 24 min</td>
        </tr>
        <tr>
            <td><span class="ticket-link">Report dashboard shows wrong totals</span><span class="ticket-id mono">TKT-2026-000120</span></td>
            <td><span class="priority high">High</span></td>
            <td class="faint">Unassigned</td>
            <td><span class="badge amber">Approaching</span></td>
            <td class="right muted">in 1 h 05 min</td>
        </tr>
        </tbody>
    </table>
</div>

<div class="grid g-2" style="margin-top:1.25rem">
    <div class="card">
        <div class="card-head"><h2>Open by priority</h2></div>
        <div class="card-body">
            <ul class="rows">
                <li><div class="row-label"><span class="priority critical">Critical</span><span class="strong">5</span></div><div class="bar"><span class="red" style="width:10%"></span></div></li>
                <li><div class="row-label"><span class="priority high">High</span><span class="strong">12</span></div><div class="bar"><span class="amber" style="width:25%"></span></div></li>
                <li><div class="row-label"><span class="priority medium">Medium</span><span class="strong">19</span></div><div class="bar"><span class="blue" style="width:40%"></span></div></li>
                <li><div class="row-label"><span class="priority">Low</span><span class="strong">12</span></div><div class="bar"><span class="gray" style="width:25%"></span></div></li>
            </ul>
        </div>
    </div>

    <div class="card table-wrap">
        <div class="card-head"><h2>By agent</h2></div>
        <table class="table">
            <thead><tr><th>Agent</th><th class="right">Open</th><th class="right">Breached</th><th class="right">Resolved</th></tr></thead>
            <tbody>
            <tr><td><div class="person"><span class="avatar sm">SA</span><span>Support Agent</span></div></td><td class="right">41</td><td class="right text-red">3</td><td class="right">126</td></tr>
            <tr><td class="faint">Unassigned</td><td class="right">7</td><td class="right">0</td><td class="right faint">&ndash;</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
