<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;

class AuditLogController extends Controller
{
	public function index()
	{
		return response()->json(['success' => true, 'data' => AuditLog::orderByDesc('survenu_le')->get()]);
	}
}


