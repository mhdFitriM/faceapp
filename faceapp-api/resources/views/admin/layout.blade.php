<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'FaceApp Admin' }}</title>
    <style>
        :root {
            --bg: #f1f5f9;
            --surface: #ffffff;
            --surface-2: #f8fafc;
            --border: #e2e8f0;
            --border-soft: #f1f5f9;
            --text: #0f172a;
            --text-2: #334155;
            --muted: #64748b;
            --muted-light: #94a3b8;
            --primary: #6366f1;
            --primary-soft: #eef2ff;
            --primary-hover: #4f46e5;
            --good: #059669;
            --good-soft: #d1fae5;
            --good-text: #065f46;
            --warn: #d97706;
            --warn-soft: #fef3c7;
            --warn-text: #92400e;
            --bad: #dc2626;
            --bad-soft: #fee2e2;
            --bad-text: #991b1b;
            --info: #0284c7;
            --info-soft: #e0f2fe;
            --info-text: #0c4a6e;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
            --shadow: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
            --radius-sm: 6px;
            --radius: 10px;
            --radius-lg: 14px;
            --radius-xl: 20px;
            --sidebar-width: 260px;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 15px; }
        body {
            font-family: "Inter", "Segoe UI", system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
            min-height: 100vh;
        }
        a { color: inherit; text-decoration: none; }
        button, input, select, textarea { font-family: inherit; font-size: inherit; }

        /* ── Layout ─────────────────────────────────────────── */
        .app-shell { display: flex; min-height: 100vh; }

        /* ── Sidebar ─────────────────────────────────────────── */
        .sidebar {
            width: var(--sidebar-width);
            flex-shrink: 0;
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
            transition: transform 0.3s ease;
        }
        .sidebar-logo {
            padding: 22px 20px 18px;
            border-bottom: 1px solid var(--border-soft);
        }
        .sidebar-logo .logo-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .logo-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--primary), #818cf8);
            border-radius: var(--radius);
            display: flex; align-items: center; justify-content: center;
            color: white;
            font-size: 16px;
            font-weight: 800;
            flex-shrink: 0;
        }
        .logo-text { font-size: 17px; font-weight: 700; color: var(--text); letter-spacing: -0.02em; }
        .logo-sub { font-size: 11px; color: var(--muted); margin-top: 1px; }
        .sidebar-nav { padding: 16px 12px; flex: 1; }
        .nav-section { margin-bottom: 4px; }
        .nav-section-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--muted-light);
            padding: 8px 10px 4px;
        }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 10px;
            border-radius: var(--radius);
            color: var(--muted);
            font-weight: 500;
            font-size: 14px;
            transition: all 0.15s;
            margin-bottom: 2px;
        }
        .nav-link:hover { background: var(--bg); color: var(--text-2); }
        .nav-link.active { background: var(--primary-soft); color: var(--primary); font-weight: 600; }
        .nav-link .nav-icon { width: 18px; height: 18px; flex-shrink: 0; opacity: 0.75; }
        .nav-link.active .nav-icon { opacity: 1; }
        .sidebar-footer {
            padding: 14px 16px;
            border-top: 1px solid var(--border-soft);
            font-size: 12px;
            color: var(--muted);
        }

        /* ── Mobile overlay ─────────────────────────────────── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 99;
        }
        .sidebar-overlay.visible { display: block; }

        /* ── Main ─────────────────────────────────────────────── */
        .main-wrap {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 14px 28px;
            display: flex;
            align-items: center;
            gap: 14px;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .topbar-hamburger {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px;
            border-radius: var(--radius-sm);
            color: var(--muted);
            flex-shrink: 0;
        }
        .topbar-hamburger:hover { background: var(--bg); color: var(--text); }
        .topbar-title { font-size: 15px; font-weight: 600; color: var(--text); }
        .topbar-spacer { flex: 1; }
        .topbar-badge {
            background: var(--primary-soft);
            color: var(--primary);
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 999px;
        }
        .content { padding: 28px; flex: 1; }

        /* ── Flash messages ─────────────────────────────────── */
        .flash {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 16px;
            border-radius: var(--radius-lg);
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
            border: 1px solid transparent;
            animation: slideDown 0.2s ease;
        }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
        .flash.good { background: var(--good-soft); color: var(--good-text); border-color: #a7f3d0; }
        .flash.bad { background: var(--bad-soft); color: var(--bad-text); border-color: #fca5a5; }

        /* ── Page header ─────────────────────────────────────── */
        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        .page-header-left h2 {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.03em;
            color: var(--text);
            line-height: 1.2;
        }
        .page-header-left p {
            margin-top: 6px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
            max-width: 600px;
        }
        .page-header-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        /* ── Stats grid ─────────────────────────────────────── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            padding: 20px;
            box-shadow: var(--shadow-sm);
        }
        .stat-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .stat-card-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
        }
        .stat-card-icon {
            width: 36px; height: 36px;
            border-radius: var(--radius);
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
        }
        .stat-card-value {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.05em;
            color: var(--text);
            line-height: 1;
        }
        .stat-card-sub {
            margin-top: 4px;
            font-size: 12px;
            color: var(--muted);
        }

        /* ── Cards ─────────────────────────────────────────────── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-sm);
        }
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 22px 16px;
            border-bottom: 1px solid var(--border-soft);
            gap: 12px;
            flex-wrap: wrap;
        }
        .card-header-left { display: flex; align-items: center; gap: 10px; }
        .card-title { font-size: 15px; font-weight: 700; color: var(--text); }
        .card-subtitle { font-size: 13px; color: var(--muted); margin-top: 2px; }
        .card-body { padding: 20px 22px; }
        .card-body.no-pad { padding: 0; }

        /* ── Buttons ─────────────────────────────────────────── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: var(--radius);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.15s;
            white-space: nowrap;
            appearance: none;
            text-decoration: none;
            line-height: 1;
        }
        .btn-primary {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        .btn-primary:hover { background: var(--primary-hover); border-color: var(--primary-hover); }
        .btn-secondary {
            background: var(--surface);
            color: var(--text-2);
            border-color: var(--border);
        }
        .btn-secondary:hover { background: var(--bg); }
        .btn-danger {
            background: var(--bad);
            color: white;
            border-color: var(--bad);
        }
        .btn-danger:hover { background: #b91c1c; }
        .btn-ghost {
            background: transparent;
            color: var(--muted);
            border-color: transparent;
        }
        .btn-ghost:hover { background: var(--bg); color: var(--text); }
        .btn-sm { padding: 6px 12px; font-size: 13px; border-radius: var(--radius-sm); }
        .btn-xs { padding: 4px 10px; font-size: 12px; border-radius: var(--radius-sm); }
        .btn-icon { padding: 7px; border-radius: var(--radius-sm); }
        .btn-icon.btn-sm { padding: 5px; }

        /* ── Status Pills ─────────────────────────────────────── */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.01em;
        }
        .badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
        .badge-good { background: var(--good-soft); color: var(--good-text); }
        .badge-good::before { background: var(--good); }
        .badge-warn { background: var(--warn-soft); color: var(--warn-text); }
        .badge-warn::before { background: var(--warn); }
        .badge-bad { background: var(--bad-soft); color: var(--bad-text); }
        .badge-bad::before { background: var(--bad); }
        .badge-info { background: var(--info-soft); color: var(--info-text); }
        .badge-info::before { background: var(--info); }
        .badge-neutral { background: var(--border-soft); color: var(--muted); }
        .badge-neutral::before { background: var(--muted-light); }
        .badge-primary { background: var(--primary-soft); color: var(--primary); }
        .badge-primary::before { background: var(--primary); }

        /* ── Table ─────────────────────────────────────────────── */
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        thead tr { border-bottom: 1px solid var(--border); }
        tbody tr { border-bottom: 1px solid var(--border-soft); transition: background 0.1s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--surface-2); }
        th {
            padding: 11px 16px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            text-align: left;
            background: var(--surface-2);
            white-space: nowrap;
        }
        th:first-child { border-radius: var(--radius-sm) 0 0 0; }
        th:last-child { border-radius: 0 var(--radius-sm) 0 0; }
        td { padding: 13px 16px; color: var(--text-2); vertical-align: middle; }
        .td-primary { font-weight: 600; color: var(--text); }
        .td-sub { font-size: 12px; color: var(--muted); margin-top: 2px; }
        .td-actions { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }

        /* ── Pagination ─────────────────────────────────────── */
        .pagination-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 22px;
            border-top: 1px solid var(--border-soft);
            gap: 12px;
            flex-wrap: wrap;
        }
        .pagination-info { font-size: 13px; color: var(--muted); }
        .pagination { display: flex; align-items: center; gap: 4px; }
        .pagination a, .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            padding: 0 8px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 500;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text-2);
            transition: all 0.15s;
        }
        .pagination a:hover { background: var(--bg); }
        .pagination .active span {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
            font-weight: 700;
        }
        .pagination .disabled span { opacity: 0.4; cursor: not-allowed; }
        .pagination svg { pointer-events: none; }

        /* ── Forms ─────────────────────────────────────────────── */
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 18px; }
        .form-grid.cols-3 { grid-template-columns: repeat(3, 1fr); }
        .form-grid.cols-1 { grid-template-columns: 1fr; }
        .col-span-2 { grid-column: 1 / -1; }
        .field { display: flex; flex-direction: column; gap: 6px; }
        .field label { font-size: 13px; font-weight: 600; color: var(--text-2); }
        .field label .req { color: var(--bad); margin-left: 2px; }
        .field-hint { font-size: 12px; color: var(--muted); }
        .input,
        input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]):not([type="file"]),
        textarea, select {
            width: 100%;
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            padding: 9px 12px;
            color: var(--text);
            font-size: 14px;
            font-family: inherit;
            line-height: 1.5;
            transition: border-color 0.15s, box-shadow 0.15s;
            appearance: none;
            -webkit-appearance: none;
        }
        .input:focus,
        input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]):not([type="file"]):focus,
        textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
        }
        .input:hover,
        input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]):not([type="file"]):hover:not(:focus),
        textarea:hover:not(:focus), select:hover:not(:focus) {
            border-color: #94a3b8;
        }
        select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 20 20' fill='%2364748b'%3E%3Cpath fill-rule='evenodd' d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z' clip-rule='evenodd'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            padding-right: 34px;
        }
        input[readonly] { background: var(--surface-2); color: var(--muted); cursor: not-allowed; }
        ::placeholder { color: var(--muted-light); }
        textarea { min-height: 100px; resize: vertical; }
        .checkbox-field { display: flex; align-items: center; gap: 10px; }
        .checkbox-field input[type="checkbox"] {
            width: 16px; height: 16px;
            border-radius: 4px;
            border: 1.5px solid var(--border);
            background: var(--surface);
            cursor: pointer;
            accent-color: var(--primary);
            flex-shrink: 0;
            padding: 0;
        }
        .checkbox-field label { font-size: 14px; font-weight: 500; color: var(--text-2); cursor: pointer; }
        .form-section-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--muted);
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-soft);
            margin-bottom: 4px;
            grid-column: 1 / -1;
        }
        .form-actions { display: flex; gap: 10px; flex-wrap: wrap; grid-column: 1 / -1; padding-top: 4px; }

        /* ── Tabs ─────────────────────────────────────────────── */
        .tabs-bar {
            display: flex;
            gap: 2px;
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 4px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            flex-wrap: nowrap;
            margin-bottom: 20px;
        }
        .tabs-bar::-webkit-scrollbar { display: none; }
        .tab-item {
            padding: 7px 14px;
            border-radius: var(--radius);
            font-size: 13px;
            font-weight: 600;
            color: var(--muted);
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.15s;
            border: 1px solid transparent;
        }
        .tab-item:hover { color: var(--text); }
        .tab-item.active { background: var(--surface); color: var(--primary); border-color: var(--border); box-shadow: var(--shadow-sm); }

        /* ── Search bar ─────────────────────────────────────── */
        .search-bar {
            display: flex;
            gap: 10px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        .search-field { position: relative; flex: 1; min-width: 200px; }
        .search-field input { padding-left: 36px; }
        .search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted-light);
            pointer-events: none;
        }

        /* ── Empty state ─────────────────────────────────────── */
        .empty-state {
            text-align: center;
            padding: 48px 20px;
        }
        .empty-icon {
            width: 56px; height: 56px;
            background: var(--surface-2);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            font-size: 24px;
        }
        .empty-state h4 { font-size: 16px; font-weight: 600; margin-bottom: 6px; }
        .empty-state p { font-size: 14px; color: var(--muted); max-width: 280px; margin: 0 auto; }

        /* ── Modal ─────────────────────────────────────────────── */
        .modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 200;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-backdrop.open { display: flex; animation: fadeIn 0.15s ease; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .modal {
            background: var(--surface);
            border-radius: var(--radius-xl);
            width: 100%;
            max-width: 680px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
            animation: scaleIn 0.15s ease;
        }
        @keyframes scaleIn { from { transform: scale(0.96); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px 16px;
            border-bottom: 1px solid var(--border);
        }
        .modal-title { font-size: 17px; font-weight: 700; }
        .modal-close {
            background: none; border: none; cursor: pointer;
            color: var(--muted); padding: 4px;
            border-radius: var(--radius-sm);
            display: flex; align-items: center; justify-content: center;
        }
        .modal-close:hover { background: var(--bg); color: var(--text); }
        .modal-body { padding: 20px 24px; }
        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            padding: 14px 24px 20px;
            border-top: 1px solid var(--border-soft);
        }

        /* ── Action response ─────────────────────────────────── */
        .response-card { margin-top: 20px; }
        .response-card details summary {
            cursor: pointer;
            font-size: 13px;
            color: var(--muted);
            font-weight: 600;
            padding: 4px 0;
        }
        .response-card details summary:hover { color: var(--text); }
        .response-card pre {
            margin-top: 10px;
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 14px;
            font-size: 12px;
            line-height: 1.5;
            overflow: auto;
            max-height: 300px;
        }

        /* ── Grid layouts ────────────────────────────────────── */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }

        /* ── Utilities ─────────────────────────────────────────── */
        .text-muted { color: var(--muted); }
        .text-sm { font-size: 13px; }
        .text-xs { font-size: 12px; }
        .font-semibold { font-weight: 600; }
        .font-bold { font-weight: 700; }
        .mb-16 { margin-bottom: 16px; }
        .mb-20 { margin-bottom: 20px; }
        .mt-20 { margin-top: 20px; }
        .gap-8 { gap: 8px; }
        .flex { display: flex; }
        .flex-wrap { flex-wrap: wrap; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .min-w-0 { min-width: 0; }
        .truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        /* ── Responsive ─────────────────────────────────────── */
        @media (max-width: 1100px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .grid-2 { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-wrap { margin-left: 0; }
            .topbar { padding: 12px 16px; }
            .topbar-hamburger { display: flex; }
            .content { padding: 16px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
            .form-grid { grid-template-columns: 1fr; }
            .form-grid.cols-3 { grid-template-columns: 1fr; }
            .page-header { flex-direction: column; align-items: flex-start; }
            .modal { border-radius: var(--radius-lg); }
        }
        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
            .topbar-badge { display: none; }
            .pagination-info { display: none; }
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <!-- Sidebar overlay for mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                <div class="logo-wrap">
                    <div class="logo-icon">F</div>
                    <div>
                        <div class="logo-text">FaceApp</div>
                        <div class="logo-sub">Admin Console</div>
                    </div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-label">Main</div>
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <svg class="nav-icon" viewBox="0 0 20 20" fill="currentColor"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zm6-4a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zm6-3a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>
                        Overview
                    </a>
                </div>
                <div class="nav-section">
                    <div class="nav-section-label">Manage</div>
                    <a href="{{ route('admin.devices.index') }}" class="nav-link {{ request()->routeIs('admin.devices.*') ? 'active' : '' }}">
                        <svg class="nav-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z" clip-rule="evenodd"/></svg>
                        Devices
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <svg class="nav-icon" viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zm8 0a3 3 0 11-6 0 3 3 0 016 0zm-4.07 11c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
                        Users
                    </a>
                </div>
                <div class="nav-section">
                    <div class="nav-section-label">System</div>
                    <a href="{{ route('admin.settings.edit') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <svg class="nav-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>
                        Settings
                    </a>
                    <a href="{{ route('devices.monitor.index') }}" class="nav-link {{ request()->routeIs('devices.monitor.*') ? 'active' : '' }}">
                        <svg class="nav-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11 4a1 1 0 10-2 0v4a1 1 0 102 0V7zm-3 1a1 1 0 10-2 0v3a1 1 0 102 0V8zM8 9a1 1 0 00-2 0v2a1 1 0 102 0V9z" clip-rule="evenodd"/></svg>
                        Callback Monitor
                    </a>
                </div>
            </nav>

            <div class="sidebar-footer">
                FaceApp &copy; {{ date('Y') }}
            </div>
        </aside>

        <!-- Main content -->
        <div class="main-wrap">
            <header class="topbar">
                <button class="topbar-hamburger" onclick="toggleSidebar()" aria-label="Toggle menu">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm1 5a1 1 0 100 2h12a1 1 0 100-2H4z" clip-rule="evenodd"/></svg>
                </button>
                <span class="topbar-title">{{ $title ?? 'FaceApp Admin' }}</span>
                <div class="topbar-spacer"></div>
                <span class="topbar-badge">Admin</span>
            </header>

            <main class="content">
                @if (session('status'))
                    <div class="flash good">
                        <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="flex-shrink:0;margin-top:1px"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        {{ session('status') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="flash bad">
                        <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="flex-shrink:0;margin-top:1px"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        {{ session('error') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="flash bad">
                        <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="flex-shrink:0;margin-top:1px"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        {{ $errors->first() }}
                    </div>
                @endif

                @yield('content')

                @if (session('action_response'))
                    <div class="card response-card">
                        <div class="card-header">
                            <div class="card-header-left">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="color:var(--muted)"><path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm14 1a1 1 0 11-2 0 1 1 0 012 0zM2 13a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2v-2zm14 1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd"/></svg>
                                <span class="card-title">Gateway Response</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <details open>
                                <summary>Raw gateway response</summary>
                                <pre>{{ json_encode(session('action_response'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            </details>
                        </div>
                    </div>
                @endif
            </main>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('visible');
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.remove('visible');
        }
        function openModal(id) { document.getElementById(id).classList.add('open'); }
        function closeModal(id) { document.getElementById(id).classList.remove('open'); }
        // Close modal on backdrop click
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-backdrop')) {
                e.target.classList.remove('open');
            }
        });
        // Close modal on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-backdrop.open').forEach(m => m.classList.remove('open'));
            }
        });
    </script>
</body>
</html>
