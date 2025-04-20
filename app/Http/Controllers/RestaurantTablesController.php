<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RestaurantTablesController extends Controller
{
    public function index()
    {
        $emptyTablesCount = $this->getEmptyTablesCount();
        $busyTablesCount = $this->getBusyTablesCount();
        $emptyTables = $this->getEmptyTables();

        return Inertia::render('RestaurantTables', [
            'emptyTablesCount' => $emptyTablesCount,
            'busyTablesCount' => $busyTablesCount,
            'emptyTables' => $emptyTables
        ]);
    }

    public function getEmptyTablesCount()
    {
        return DB::table('table_states')
            ->where('table_status', 'empty')
            ->whereDate('timestamp', now()->toDateString())
            ->count();
    }

    public function getBusyTablesCount()
    {
        return DB::table('table_states')
            ->where('table_status', 'busy')
            ->whereDate('timestamp', now()->toDateString())
            ->count();
    }

    public function getEmptyTables()
    {
        // Join with resto_table to get seat capacity information
        return DB::table('table_states')
            ->join('resto_table', 'table_states.fk_table_name', '=', 'resto_table.table_name')
            ->select('table_states.fk_table_name as table_name', 'resto_table.seat_capacity')
            ->where('table_states.table_status', 'empty')
            ->whereDate('table_states.timestamp', now()->toDateString())
            ->get();
    }

    public function getBusyTables()
    {
        // Join with resto_table to get seat capacity information
        return DB::table('table_states')
            ->join('resto_table', 'table_states.fk_table_name', '=', 'resto_table.table_name')
            ->select('table_states.fk_table_name as table_name', 'resto_table.seat_capacity', 'table_states.guest_count')
            ->where('table_states.table_status', 'busy')
            ->whereDate('table_states.timestamp', now()->toDateString())
            ->get();
    }

    public function fetchEmptyTablesCount()
    {
        return response()->json([
            'emptyTablesCount' => $this->getEmptyTablesCount()
        ]);
    }

    public function fetchBusyTablesCount()
    {
        return response()->json([
            'busyTablesCount' => $this->getBusyTablesCount()
        ]);
    }

    public function fetchAllTableCounts()
    {
        return response()->json([
            'emptyTablesCount' => $this->getEmptyTablesCount(),
            'busyTablesCount' => $this->getBusyTablesCount()
        ]);
    }

    public function fetchEmptyTables()
    {
        return response()->json([
            'emptyTables' => $this->getEmptyTables()
        ]);
    }

    public function fetchBusyTables()
    {
        return response()->json([
            'busyTables' => $this->getBusyTables()
        ]);
    }

    public function assignTable(Request $request)
    {
        // Simple validation
        $validated = $request->validate([
            'tableName' => 'required|string',
            'guestCount' => 'required|integer|min:1'
        ]);

        // Update the table status to busy with the specified guest count
        DB::table('table_states')
            ->where('fk_table_name', $validated['tableName'])
            ->update([
                'table_status' => 'busy',
                'guest_count' => $validated['guestCount'],
                'timestamp' => now()->toDateTimeString()
            ]);

        // Return updated counts and table status
        return response()->json([
            'success' => true,
            'message' => 'Table assigned successfully',
            'emptyTablesCount' => $this->getEmptyTablesCount(),
            'busyTablesCount' => $this->getBusyTablesCount(),
            'emptyTables' => $this->getEmptyTables(),
            'busyTables' => $this->getBusyTables()
        ]);
    }

    public function releaseTable(Request $request)
    {
        // Simple validation
        $validated = $request->validate([
            'tableName' => 'required|string',
        ]);

        // Update the table status to empty
        DB::table('table_states')
            ->where('fk_table_name', $validated['tableName'])
            ->update([
                'table_status' => 'empty',
                'guest_count' => 0,
                'timestamp' => now()->toDateTimeString()
            ]);

        // Return updated counts and table status
        return response()->json([
            'success' => true,
            'message' => 'Table released successfully',
            'emptyTablesCount' => $this->getEmptyTablesCount(),
            'busyTablesCount' => $this->getBusyTablesCount(),
            'emptyTables' => $this->getEmptyTables(),
            'busyTables' => $this->getBusyTables()
        ]);
    }
}
