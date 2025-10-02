<?php

namespace Core;

class Toolbar
{
    /**
     * @param array $data
     */
    public function __construct( protected array $data )
    {}
    public function render(): string
    {
        // Extract data for easier access in the HEREDOC
        $appVersion   = htmlspecialchars( $this->data['app_version'] ?? 'N/A', ENT_QUOTES, 'UTF-8' );
        $phpVersion   = htmlspecialchars( $this->data['php_version'] ?? 'N/A', ENT_QUOTES, 'UTF-8' );
        $execTime     = $this->data['execution_time'] ?? '0';
        $memUsage     = $this->data['memory_usage'] ?? '0';
        $responseCode = $this->data['response_code'] ?? 'N/A';
        $routeInfo    = 'No route matched';
        if ( isset( $this->data['route'] ) ) {
            $r         = $this->data['route'];
            $routeInfo = strtoupper( $r['method'] ) . ' ' . htmlspecialchars( $r['path'], ENT_QUOTES, 'UTF-8' ) . ' -> ' . htmlspecialchars( $r['controller'] . '@' . $r['action'], ENT_QUOTES, 'UTF-8' );
        }
        $sessionData = json_encode( $this->data['session'] ?? [], JSON_PRETTY_PRINT );

        return <<<HTML
<style>
    #rhapsody-debug-toolbar {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background-color: #1F2937;
        color: #F9FAFB;
        z-index: 99999;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        font-size: 14px;
        display: flex;
        align-items: stretch;
        height: 35px;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.3);
    }
    #rhapsody-debug-toolbar .toolbar-item {
        display: flex;
        align-items: center;
        padding: 0 15px;
        border-right: 1px solid #4B5563;
        cursor: pointer;
    }
    #rhapsody-debug-toolbar .toolbar-item:hover {
        background-color: #374151;
    }
    #rhapsody-debug-toolbar .toolbar-label {
        font-weight: 600;
        margin-right: 8px;
    }
    #rhapsody-debug-toolbar .toolbar-value {
        color: #9CA3AF;
    }
    #rhapsody-debug-toolbar .status-ok { color: #10B981; }
    #rhapsody-debug-toolbar .status-redirect { color: #F59E0B; }
    #rhapsody-debug-toolbar .status-error { color: #EF4444; }
    #rhapsody-debug-toolbar .toolbar-logo {
        font-weight: bold;
        background: #4f46e5;
    }
    #debug-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.7);
        z-index: 99998;
        display: none;
    }
    #debug-modal-content {
        position: fixed;
        bottom: 50px;
        left: 50%;
        transform: translateX(-50%);
        background: #1F2937;
        border: 1px solid #4B5563;
        border-radius: 8px;
        width: 80%;
        max-width: 800px;
        max-height: 70vh;
        overflow-y: auto;
        color: #F9FAFB;
        padding: 20px;
    }
</style>

<div id="debug-modal-overlay">
    <div id="debug-modal-content">
        <h3 style="font-size: 1.5rem; font-weight: bold; border-bottom: 1px solid #4B5563; padding-bottom: 10px; margin-bottom: 10px;">Session Data</h3>
        <pre><code id="debug-session-data"></code></pre>
    </div>
</div>

<div id="rhapsody-debug-toolbar">
    <div class="toolbar-item toolbar-logo">Rhapsody {$appVersion}</div>
    <div class="toolbar-item"><span class="toolbar-label">PHP:</span> <span class="toolbar-value">{$phpVersion}</span></div>
    <div class="toolbar-item"><span class="toolbar-label">Time:</span> <span class="toolbar-value">{$execTime} ms</span></div>
    <div class="toolbar-item"><span class="toolbar-label">Memory:</span> <span class="toolbar-value">{$memUsage} MB</span></div>
    <div class="toolbar-item"><span class="toolbar-label">Route:</span> <span class="toolbar-value">{$routeInfo}</span></div>
    <div class="toolbar-item" id="toolbar-session-btn"><span class="toolbar-label">Session</span></div>
    <div class="toolbar-item" style="margin-left: auto; border-right: none;"><span class="toolbar-label">Status:</span> <span class="toolbar-value status-ok">{$responseCode}</span></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sessionBtn = document.getElementById('toolbar-session-btn');
        const modalOverlay = document.getElementById('debug-modal-overlay');
        const sessionDataEl = document.getElementById('debug-session-data');

        if (sessionBtn) {
            sessionBtn.addEventListener('click', () => {
                sessionDataEl.textContent = JSON.stringify({$sessionData}, null, 2);
                modalOverlay.style.display = 'block';
            });
            modalOverlay.addEventListener('click', () => {
                modalOverlay.style.display = 'none';
            });
        }
    });
</script>
HTML;
    }
}
