<div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="flex justify-between items-center mb-4 no-print">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Anteprima XML Completo</h3>
        <button 
            type="button"
            onclick="window.print()"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            style="background-color: rgb(var(--primary-600));"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Stampa
        </button>
    </div>

    <div id="printable-xml-content" class="xml-preview-container overflow-x-auto text-gray-900 dark:text-gray-100">
        {!! $getState() !!}
    </div>

    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #printable-xml-content, #printable-xml-content * {
                visibility: visible;
            }
            #printable-xml-content {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                background: white !important;
                color: black !important;
            }
            .no-print {
                display: none !important;
            }
            .xml-table {
                width: 100% !important;
                border: 1px solid #000 !important;
                color: #000 !important;
            }
            .xml-table td {
                border: 1px solid #000 !important;
            }
        }
        
        .xml-table {
            border-collapse: collapse;
            width: 100%;
        }
        
        .xml-table td {
            border: 1px solid #ddd;
            padding: 4px;
            vertical-align: top;
        }
        
        .dark .xml-table td {
            border-color: #4b5563;
        }
    </style>
</div>
