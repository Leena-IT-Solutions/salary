<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Setting;

class MachineConfigController extends Controller
{
    public function index()
    {
        $machineToken = Setting::where('key', 'Attendance Machine API Token')->first()?->value ?? '';
        return view('settings.configure_machine', compact('machineToken'));
    }

    public function sendUdpConfig(Request $request)
    {
        $request->validate([
            'target_ip' => 'nullable|string',
            'command'   => 'required|string',
        ]);

        $ip = $request->target_ip ?: '255.255.255.255'; // Broadcast by default
        $port = 7778;
        $message = $request->command;

        try {
            $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1);
            socket_sendto($sock, $message, strlen($message), 0, $ip, $port);
            socket_close($sock);

            return response()->json([
                'status' => 'success',
                'message' => "UDP command [{$message}] sent to {$ip}:{$port}"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'UDP send failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function proxyApiCall(Request $request)
    {
        $request->validate([
            'machine_ip' => 'required|string',
            'endpoint'   => 'required|string',
            'method'     => 'required|string',
            'user'       => 'nullable|string',
            'pass'       => 'nullable|string',
        ]);

        $ip = $request->machine_ip;
        $endpoint = ltrim($request->endpoint, '/');
        $url = "http://{$ip}/{$endpoint}";
        $user = $request->user ?: 'admin';
        $pass = $request->pass ?: 'changeme';
        $method = strtoupper($request->method);
        $data = $request->except(['machine_ip', 'endpoint', 'method', 'user', 'pass']);

        try {
            $client = Http::timeout(6)->withBasicAuth($user, $pass);

            if ($method === 'POST') {
                $response = $client->asForm()->post($url, $data);
            } else {
                $response = $client->get($url, $data);
            }

            return response()->json($response->json() ?? ['raw' => $response->body()], $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Connection to NodeMCU failed: ' . $e->getMessage()
            ], 502);
        }
    }
}
