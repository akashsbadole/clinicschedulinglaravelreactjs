<?php

namespace App\Http\Controllers;

use App\Console\Commands\SendAppointmentReminders;
use App\Console\Commands\SendOverduePaymentNotifications;
use App\Console\Commands\SendPaymentReminders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get user's notifications
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = $user->notifications();

        // Filter by type if provided
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by read status
        if ($request->has('read')) {
            $read = $request->boolean('read');
            if ($read) {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        }

        // Sort by created date (default)
        $query->orderBy('created_at', 'desc');

        // Pagination
        $notifications = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully'
        ]);
    }

    /**
     * Get notification statistics
     */
    public function getStats()
    {
        $user = Auth::user();

        $stats = [
            'total' => $user->notifications()->count(),
            'unread' => $user->unreadNotifications()->count(),
            'read' => $user->notifications()->whereNotNull('read_at')->count(),
            'appointment_notifications' => $user->notifications()->where('type', 'like', '%Appointment%')->count(),
            'payment_notifications' => $user->notifications()->where('type', 'like', '%Payment%')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Send appointment reminders manually
     */
    public function sendAppointmentReminders(Request $request)
    {
        $user = Auth::user();

        // Only admins and staff can send reminders
        if (!$user->isAdmin() && !$user->isStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to send reminders'
            ], 403);
        }

        $hours = $request->get('hours', 24);

        try {
            Artisan::call(SendAppointmentReminders::class, [
                '--hours' => $hours
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Appointment reminders sent successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send appointment reminders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send payment reminders manually
     */
    public function sendPaymentReminders(Request $request)
    {
        $user = Auth::user();

        // Only admins and staff can send reminders
        if (!$user->isAdmin() && !$user->isStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to send reminders'
            ], 403);
        }

        $days = $request->get('days', 7);

        try {
            Artisan::call(SendPaymentReminders::class, [
                '--days' => $days
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment reminders sent successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send payment reminders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send overdue payment notifications manually
     */
    public function sendOverdueNotifications(Request $request)
    {
        $user = Auth::user();

        // Only admins and staff can send notifications
        if (!$user->isAdmin() && !$user->isStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to send notifications'
            ], 403);
        }

        $days = $request->get('days', 1);

        try {
            Artisan::call(SendOverduePaymentNotifications::class, [
                '--days' => $days
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Overdue payment notifications sent successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send overdue notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
