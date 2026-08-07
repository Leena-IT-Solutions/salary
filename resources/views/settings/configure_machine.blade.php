@extends('layouts.newapp')

@section('head')
<title>Configure Attendance Machine | SalaryManager</title>
<style>
    .machine-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        padding: 24px;
        margin-bottom: 24px;
        border: 1px solid #e5e7eb;
    }
    .machine-card h4 {
        color: #1e3a5f;
        font-weight: 700;
        margin-bottom: 16px;
        border-bottom: 2px solid #edf2f7;
        padding-bottom: 8px;
    }
    .badge-online { background: #d1fae5; color: #065f46; padding: 4px 12px; border-radius: 20px; font-weight: bold; }
    .badge-offline { background: #fee2e2; color: #991b1b; padding: 4px 12px; border-radius: 20px; font-weight: bold; }
    .nav-pills .nav-link.active { background-color: #1e3a5f !important; }
</style>
@endsection

@section('content')

<page-header title="Configure Attendance Machine"></page-header>

<div class="container-fluid px-4 py-4" id="machineApp">
    
    <!-- Connection Mode Explainer Alert -->
    <div class="alert alert-info border-0 shadow-sm mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3 fs-3">⚡</div>
            <div>
                <strong class="d-block">1-Click SmartConfig Provisioner Built-In:</strong>
                Use the <strong>SmartConfig (ESP-Touch)</strong> tab below to send Wi-Fi credentials over the air directly to NodeMCU terminals without changing your phone/laptop Wi-Fi network!
            </div>
        </div>
    </div>

    <!-- Machine Connector Bar -->
    <div class="machine-card">
        <h4>📡 Terminal Connection Setup</h4>
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label font-weight-bold">Terminal IP Address:</label>
                <input type="text" id="machine_ip" class="form-control" value="192.168.4.1" placeholder="192.168.4.1 or 192.168.1.100">
            </div>
            <div class="col-md-3">
                <label class="form-label font-weight-bold">Admin Username:</label>
                <input type="text" id="portal_user" class="form-control" value="admin">
            </div>
            <div class="col-md-3">
                <label class="form-label font-weight-bold">Admin Password:</label>
                <input type="password" id="portal_pass" class="form-control" value="changeme">
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-primary w-100 font-weight-bold" onclick="fetchStatus()">
                    🔌 Connect & Fetch Status
                </button>
            </div>
        </div>
        <div id="connectionStatus" class="mt-3"></div>
    </div>

    <!-- Main Navigation Tabs -->
    <ul class="nav nav-pills mb-4" id="configTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active font-weight-bold" id="tab-smartconfig-tab" data-bs-toggle="pill" data-bs-target="#tab-smartconfig" type="button">⚡ SmartConfig (ESP-Touch)</button>
        </li>
        <li class="nav-item">
            <button class="nav-link font-weight-bold" id="tab-wifi-tab" data-bs-toggle="pill" data-bs-target="#tab-wifi" type="button">📶 Wi-Fi Credentials</button>
        </li>
        <li class="nav-item">
            <button class="nav-link font-weight-bold" id="tab-settings-tab" data-bs-toggle="pill" data-bs-target="#tab-settings" type="button">⚙️ Terminal Settings</button>
        </li>
        <li class="nav-item">
            <button class="nav-link font-weight-bold" id="tab-write-tab" data-bs-toggle="pill" data-bs-target="#tab-write" type="button">💳 Write RFID Card</button>
        </li>
        <li class="nav-item">
            <button class="nav-link font-weight-bold" id="tab-queue-tab" data-bs-toggle="pill" data-bs-target="#tab-queue" type="button">📋 Offline Queue</button>
        </li>
    </ul>

    <div class="tab-content">

        <!-- TAB 1: SMARTCONFIG (ESP-TOUCH) -->
        <div class="tab-pane fade show active" id="tab-smartconfig">
            <div class="machine-card">
                <h4>⚡ 1-Click SmartConfig Provisioner (ESP-Touch)</h4>
                <p class="text-muted">Broadcast Wi-Fi credentials over UDP multicast/broadcast directly to NodeMCU terminals in range without disconnecting from <strong>payroll.sarvodayavidyalay.com</strong>.</p>
                <form id="smartConfigForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Target Wi-Fi Network SSID:</label>
                            <input type="text" id="sc_ssid" class="form-control" value="LITS" placeholder="Enter Wi-Fi Name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Target Wi-Fi Network Password:</label>
                            <input type="password" id="sc_pass" class="form-control" value="Prayer@2025" placeholder="Enter Wi-Fi Password" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label font-weight-bold">Target Broadcast IP (Default: 255.255.255.255):</label>
                            <input type="text" id="sc_target" class="form-control" value="255.255.255.255">
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-warning font-weight-bold px-4 py-2 text-dark fs-6">
                                ⚡ Start SmartConfig Provisioning
                            </button>
                        </div>
                    </div>
                </form>
                <div id="smartConfigStatus" class="mt-3"></div>
            </div>
        </div>

        <!-- TAB 2: WI-FI CREDENTIALS -->
        <div class="tab-pane fade" id="tab-wifi">
            <div class="machine-card">
                <h4>📶 Direct Wi-Fi Network Credentials</h4>
                <p class="text-muted">Directly apply Wi-Fi SSID and Password to a connected NodeMCU terminal.</p>
                <form id="wifiForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Wi-Fi Network SSID:</label>
                            <input type="text" id="wifi_ssid" class="form-control" value="LITS" placeholder="Enter Wi-Fi Name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Wi-Fi Network Password:</label>
                            <input type="password" id="wifi_pass" class="form-control" value="Prayer@2025" placeholder="Enter Wi-Fi Password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Hotspot Access Point Name (AP SSID):</label>
                            <input type="text" id="ap_ssid" class="form-control" value="attendance">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Hotspot Access Point Password:</label>
                            <input type="password" id="ap_pass" class="form-control" value="password">
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-success font-weight-bold px-4">
                                🚀 Save & Apply Wi-Fi Credentials Live
                            </button>
                        </div>
                    </div>
                </form>
                <div id="wifiStatus" class="mt-3"></div>
            </div>
        </div>

        <!-- TAB 3: TERMINAL SETTINGS -->
        <div class="tab-pane fade" id="tab-settings">
            <div class="machine-card">
                <h4>⚙️ Live Terminal Settings</h4>
                <form id="settingsForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Company / Institution Name:</label>
                            <input type="text" id="company_name" class="form-control" placeholder="Sarvodaya Vidyalay">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Operation Mode:</label>
                            <select id="op_mode" class="form-select">
                                <option value="1">Read (R) - Normal Attendance</option>
                                <option value="0">Setup (S) - Diagnostic Mode</option>
                                <option value="2">Write (W) - Card Burning</option>
                                <option value="3">Format (F) - Format Card</option>
                                <option value="4">Delete (D) - Clear Data</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Audio Beep Effects:</label>
                            <select id="buzzer_enabled" class="form-select">
                                <option value="1">Enabled (Sound On)</option>
                                <option value="0">Muted (Silent Mode)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Host URI Endpoint:</label>
                            <input type="text" id="host_uri" class="form-control" value="https://payroll.sarvodayavidyalay.com/attendance/save" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label font-weight-bold">Bearer API Access Token:</label>
                            <input type="password" id="api_token" class="form-control" value="{{ $machineToken }}" placeholder="Bearer Token">
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary font-weight-bold px-4">
                                💾 Save Settings Live (No Reboot)
                            </button>
                        </div>
                    </div>
                </form>
                <div id="settingsStatus" class="mt-3"></div>
            </div>
        </div>

        <!-- TAB 4: WRITE RFID CARD -->
        <div class="tab-pane fade" id="tab-write">
            <div class="machine-card">
                <h4>💳 Write / Burn RFID Card</h4>
                <p class="text-muted">Arm the terminal to burn an Employee Code onto an RFID card block 4 when tapped.</p>
                <form id="writeForm">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label font-weight-bold">Card Value / Employee Code:</label>
                            <input type="text" id="card_val" class="form-control" placeholder="e.g. SV002" maxlength="20" required>
                        </div>
                        <div class="col-md-4 align-self-end">
                            <button type="submit" class="btn btn-success w-100 font-weight-bold">
                                💳 Arm Write Card
                            </button>
                        </div>
                    </div>
                </form>
                <div id="writeStatus" class="mt-3 font-weight-bold"></div>
            </div>
        </div>

        <!-- TAB 5: OFFLINE QUEUE -->
        <div class="tab-pane fade" id="tab-queue">
            <div class="machine-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">📋 Offline Punches Queue</h4>
                    <div>
                        <button class="btn btn-secondary btn-sm" onclick="fetchQueue()">🔄 Refresh Queue</button>
                        <button class="btn btn-danger btn-sm" onclick="clearQueue()">🗑️ Clear Queue</button>
                    </div>
                </div>
                <div id="queueTableBox">
                    <p class="text-muted">Click 'Refresh Queue' to inspect stored offline punches.</p>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
const CSRF_TOKEN = '{{ csrf_token() }}';

function makeRequest(endpoint, method, data = {}) {
    const ip = document.getElementById('machine_ip').value;
    const user = document.getElementById('portal_user').value;
    const pass = document.getElementById('portal_pass').value;

    const directUrl = `http://${ip}/${endpoint.replace(/^\//, '')}`;
    const authHeader = 'Basic ' + btoa(user + ':' + pass);

    let formBody = new URLSearchParams();
    for (let k in data) formBody.append(k, data[k]);

    const fetchOptions = {
        method: method,
        headers: {
            'Authorization': authHeader,
            ...(method === 'POST' ? { 'Content-Type': 'application/x-www-form-urlencoded' } : {})
        },
        ...(method === 'POST' ? { body: formBody } : {})
    };

    return fetch(directUrl, fetchOptions)
        .then(r => r.json())
        .catch(err => {
            return fetch('/application_settings/configure_machine/proxy_api', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({
                    machine_ip: ip,
                    user: user,
                    pass: pass,
                    endpoint: endpoint,
                    method: method,
                    ...data
                })
            }).then(r => r.json());
        });
}

function fetchStatus() {
    const box = document.getElementById('connectionStatus');
    box.innerHTML = '<span class="badge-offline">⏳ Connecting to NodeMCU...</span>';

    makeRequest('/api/status', 'GET')
        .then(data => {
            if (data.status === 'online') {
                box.innerHTML = `<span class="badge-online">✓ Connected (${data.ip}) | RSSI: ${data.rssi} dBm | Company: ${data.company_name}</span>`;
                document.getElementById('company_name').value = data.company_name || '';
                document.getElementById('host_uri').value = data.host_uri || 'https://payroll.sarvodayavidyalay.com/attendance/save';
                document.getElementById('api_token').value = data.api_token || '';
                document.getElementById('op_mode').value = data.op_mode;
                document.getElementById('buzzer_enabled').value = data.buzzer_enabled;
                if (data.wifi_ssid) {
                    document.getElementById('wifi_ssid').value = data.wifi_ssid;
                    document.getElementById('sc_ssid').value = data.wifi_ssid;
                }
            } else {
                box.innerHTML = `<span class="badge-offline">❌ Error: ${data.message || 'Connection failed'}</span>`;
            }
        })
        .catch(() => {
            box.innerHTML = `<span class="badge-offline">❌ Connection failed - Check IP address.</span>`;
        });
}

document.getElementById('smartConfigForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const ssid = document.getElementById('sc_ssid').value;
    const pass = document.getElementById('sc_pass').value;
    const target = document.getElementById('sc_target').value || '255.255.255.255';
    const statusBox = document.getElementById('smartConfigStatus');

    statusBox.innerHTML = '<div class="alert alert-info">⚡ Transmitting SmartConfig UDP bursts across local network... Keep terminal powered on!</div>';

    fetch('/application_settings/configure_machine/smartconfig_provision', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN
        },
        body: JSON.stringify({
            wifi_ssid: ssid,
            wifi_pass: pass,
            target_ip: target
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            statusBox.innerHTML = `<div class="alert alert-success">✓ ${data.message}<br><small>NodeMCU terminal will capture credentials, chime with a success tone, and connect to Wi-Fi!</small></div>`;
        } else {
            statusBox.innerHTML = `<div class="alert alert-danger">❌ ${data.message}</div>`;
        }
    })
    .catch(() => {
        statusBox.innerHTML = `<div class="alert alert-danger">❌ Failed to transmit SmartConfig.</div>`;
    });
});

document.getElementById('wifiForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const statusBox = document.getElementById('wifiStatus');
    statusBox.innerHTML = '⏳ Applying Wi-Fi credentials...';

    makeRequest('/api/wifi', 'POST', {
        wifi_ssid: document.getElementById('wifi_ssid').value,
        wifi_pass: document.getElementById('wifi_pass').value,
        ap_ssid: document.getElementById('ap_ssid').value,
        ap_pass: document.getElementById('ap_pass').value
    })
    .then(data => {
        statusBox.innerHTML = `<div class="alert alert-success">✓ ${data.message || 'Wi-Fi credentials saved and applied live!'}</div>`;
    })
    .catch(() => {
        statusBox.innerHTML = `<div class="alert alert-danger">❌ Failed to apply Wi-Fi credentials.</div>`;
    });
});

document.getElementById('settingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const statusBox = document.getElementById('settingsStatus');
    statusBox.innerHTML = '⏳ Saving settings live...';

    makeRequest('/api/config', 'POST', {
        company_name: document.getElementById('company_name').value,
        host_uri: document.getElementById('host_uri').value,
        api_token: document.getElementById('api_token').value,
        op_mode: document.getElementById('op_mode').value,
        buzzer_enabled: document.getElementById('buzzer_enabled').value
    })
    .then(data => {
        statusBox.innerHTML = `<div class="alert alert-success">✓ ${data.message}</div>`;
    })
    .catch(() => {
        statusBox.innerHTML = `<div class="alert alert-danger">❌ Failed to save settings.</div>`;
    });
});

document.getElementById('writeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const val = document.getElementById('card_val').value;
    const statusBox = document.getElementById('writeStatus');
    statusBox.style.color = '#1e3a5f';
    statusBox.innerHTML = '⏳ Arming card write mode...';

    makeRequest('/api/write', 'POST', { card_val: val })
    .then(data => {
        statusBox.innerHTML = `⏳ Armed! Place RFID card near terminal reader...`;
        pollWriteStatus();
    });
});

function pollWriteStatus() {
    makeRequest('/api/write_status', 'GET').then(data => {
        const statusBox = document.getElementById('writeStatus');
        if (data.status === 'success') {
            statusBox.style.color = '#10b981';
            statusBox.innerHTML = `✓ Card written successfully: ${data.value}`;
        } else if (data.status === 'failed') {
            statusBox.style.color = '#dc2626';
            statusBox.innerHTML = `❌ Write failed - try tapping card again.`;
        } else if (data.status === 'armed') {
            setTimeout(pollWriteStatus, 1500);
        }
    });
}

function fetchQueue() {
    const box = document.getElementById('queueTableBox');
    box.innerHTML = '⏳ Fetching queue...';

    makeRequest('/api/queue', 'GET').then(data => {
        if (!Array.isArray(data) || data.length === 0) {
            box.innerHTML = '<div class="alert alert-success mb-0">✓ Queue is empty. All punches synced!</div>';
            return;
        }
        let html = '<table class="table table-striped table-bordered"><thead><tr><th>Employee</th><th>Tag ID</th><th>Date</th><th>Time</th></tr></thead><tbody>';
        data.forEach(item => {
            html += `<tr><td>${item.tagms}</td><td>${item.tagid}</td><td>${item.date}</td><td>${item.time}</td></tr>`;
        });
        html += '</tbody></table>';
        box.innerHTML = html;
    });
}

function clearQueue() {
    if (!confirm('Clear all queued offline punches?')) return;
    makeRequest('/api/queue/clear', 'POST').then(data => {
        fetchQueue();
    });
}
</script>
@endsection
