<style>
    /* FullCalendar Custom Premium Styling */
    .fc {
        --fc-border-color: #f3f4f6;
        --fc-daygrid-event-dot-width: 8px;
        --fc-button-bg-color: #ffffff;
        --fc-button-border-color: #e5e7eb;
        --fc-button-text-color: #374151;
        --fc-button-active-bg-color: #1E5128;
        --fc-button-active-border-color: #1E5128;
        --fc-button-hover-bg-color: #f9fafb;
        --fc-button-hover-border-color: #d1d5db;
        --fc-today-bg-color: rgba(30, 81, 40, 0.04);
        --fc-event-border-color: transparent;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    .fc .fc-toolbar-title {
        font-size: 1.1rem !important;
        font-weight: 800 !important;
        color: #1f2937;
        letter-spacing: -0.02em;
    }
    @media (min-width: 768px) {
        .fc .fc-toolbar-title {
            font-size: 1.5rem !important;
        }
    }
    .fc .fc-button {
        padding: 0.5rem 0.875rem !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        border-radius: 0.75rem !important;
        text-transform: capitalize !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    }
    .fc .fc-button-primary:not(:disabled).fc-button-active, 
    .fc .fc-button-primary:not(:disabled):active {
        background-color: #1E5128 !important;
        border-color: #1E5128 !important;
        color: #ffffff !important;
    }
    .fc .fc-button-group {
        gap: 6px !important;
    }
    .fc .fc-button-group > .fc-button {
        border-radius: 0.75rem !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
    .fc .fc-event {
        padding: 4px 8px !important;
        border-radius: 8px !important;
        font-size: 0.7rem !important;
        font-weight: 700 !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02) !important;
        border: none !important;
        transition: transform 0.15s ease, opacity 0.15s ease !important;
    }
    .fc .fc-event:hover {
        transform: scale(1.02);
        opacity: 0.95;
    }
    .fc .fc-daygrid-day-number {
        font-size: 0.825rem !important;
        font-weight: 700 !important;
        color: #4b5563 !important;
        padding: 6px !important;
    }
    .fc .fc-col-header-cell-cushion {
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        color: #9ca3af !important;
        text-transform: uppercase !important;
        padding: 8px 0 !important;
    }
    .fc-scroller {
        scrollbar-width: thin;
    }
    .fc-scroller::-webkit-scrollbar {
        width: 4px;
        height: 4px;
    }
    .fc-scroller::-webkit-scrollbar-track {
        background: transparent;
    }
    .fc-scroller::-webkit-scrollbar-thumb {
        background: #e5e7eb;
        border-radius: 10px;
    }
    
    /* Mobile toolbar spacing and adjustments */
    @media (max-width: 768px) {
        .fc .fc-toolbar {
            display: flex !important;
            flex-direction: row !important;
            justify-content: space-between !important;
            align-items: center !important;
            gap: 0.25rem !important;
            margin-bottom: 1rem !important;
        }
        .fc .fc-toolbar-title {
            font-size: 0.95rem !important;
            text-align: center !important;
            flex: 1 !important;
        }
        .fc .fc-button {
            padding: 0.35rem 0.5rem !important;
            font-size: 0.7rem !important;
        }
        .fc .fc-daygrid-day {
            min-height: 48px !important;
        }
        .fc .fc-daygrid-day-frame {
            min-height: 48px !important;
        }
        .fc .fc-daygrid-day-number {
            font-size: 0.75rem !important;
            padding: 4px !important;
        }
        .fc .fc-event {
            padding: 2px 4px !important;
            font-size: 0.6rem !important;
            border-radius: 4px !important;
        }
    }

    /* Custom Keyframe entrance for list items */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(12px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.4s ease forwards;
    }
</style>
