<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IpFilter
{
    /**
     * List of blocked IP addresses.
     * In production, this should be configurable via database or config file.
     */
    protected $blockedIps = [
        // Add blocked IPs here
        // '192.168.1.1',
        // '10.0.0.1',
    ];

    /**
     * List of allowed IP addresses (whitelist mode).
     * If not empty, only these IPs are allowed.
     */
    protected $allowedIps = [
        // Add allowed IPs here for whitelist mode
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $clientIp = $request->ip();

        // Check if IP is blocked
        if (in_array($clientIp, $this->blockedIps)) {
            ActivityLog::log('ip_blocked', 'Access blocked from blocked IP address', [
                'ip_address' => $clientIp,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ]);

            return response()->json([
                'message' => 'Access denied from this IP address.',
            ], 403);
        }

        // Check whitelist mode
        if (!empty($this->allowedIps) && !in_array($clientIp, $this->allowedIps)) {
            ActivityLog::log('ip_not_allowed', 'Access denied from non-whitelisted IP address', [
                'ip_address' => $clientIp,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ]);

            return response()->json([
                'message' => 'Access denied. IP address not in whitelist.',
            ], 403);
        }

        return $next($request);
    }
}
