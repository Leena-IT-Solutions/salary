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

    /**
     * Native ESP-Touch / SmartConfig & UDP Multicast Provisioner
     */
    public function smartConfigProvision(Request $request)
    {
        $request->validate([
            'wifi_ssid' => 'required|string',
            'wifi_pass' => 'required|string',
            'target_ip' => 'nullable|string',
        ]);

        $ssid = $request->wifi_ssid;
        $pass = $request->wifi_pass;
        $ip = $request->target_ip ?: '255.255.255.255';

        try {
            $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1);

            // 1. Send plain-text UDP command format
            $cmd1 = "SET_WIFI:{$ssid}:{$pass}";
            socket_sendto($sock, $cmd1, strlen($cmd1), 0, $ip, 7778);

            // 2. Send JSON command format
            $jsonCmd = json_encode(['wifi_ssid' => $ssid, 'wifi_pass' => $pass]);
            socket_sendto($sock, $jsonCmd, strlen($jsonCmd), 0, $ip, 7778);

            // 3. Send ESP-Touch length-sequence multicast bursts on ports 7001, 10000 & 7778
            $payload = $pass . $ssid;
            $dataBytes = unpack('C*', $payload);

            // Esptouch guide prefix sequence (515, 514, 513, 512 bytes)
            $guideLengths = [515, 514, 513, 512];
            for ($loop = 0; $loop < 3; $loop++) {
                foreach ($guideLengths as $gLen) {
                    $dummy = str_repeat('A', $gLen);
                    socket_sendto($sock, $dummy, strlen($dummy), 0, $ip, 7001);
                    socket_sendto($sock, $dummy, strlen($dummy), 0, $ip, 10000);
                    socket_sendto($sock, $dummy, strlen($dummy), 0, $ip, 7778);
                    usleep(2000);
                }

                // Encoded payload bytes
                foreach ($dataBytes as $idx => $b) {
                    $packetLen = 40 + $b; // Esptouch offset
                    $dummy = str_repeat('B', $packetLen);
                    socket_sendto($sock, $dummy, strlen($dummy), 0, $ip, 7001);
                    socket_sendto($sock, $dummy, strlen($dummy), 0, $ip, 10000);
                    socket_sendto($sock, $dummy, strlen($dummy), 0, $ip, 7778);
                    usleep(3000);
                }
            }

            socket_close($sock);

            return response()->json([
                'status' => 'success',
                'message' => "SmartConfig & UDP bursts transmitted for SSID [{$ssid}]"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'SmartConfig transmission failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sendUdpConfig(Request $request)
    {
        $request->validate([
            'target_ip' => 'nullable|string',
            'command'   => 'required|string',
        ]);

        $ip = $request->target_ip ?: '255.255.255.255';
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
