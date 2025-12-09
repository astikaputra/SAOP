{{-- resources/views/loket/debug.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Debug Loket
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Debug Information</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <strong>User Info:</strong>
                            <pre>{{ json_encode(auth()->user(), JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        
                        <div>
                            <strong>Database Columns:</strong>
                            <pre><?php 
                                try {
                                    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('counters');
                                    echo json_encode($columns, JSON_PRETTY_PRINT);
                                } catch (\Exception $e) {
                                    echo "Error: " . $e->getMessage();
                                }
                            ?></pre>
                        </div>
                        
                        <div>
                            <strong>All Counters:</strong>
                            <pre><?php 
                                try {
                                    $counters = \App\Models\Counter::all();
                                    echo json_encode($counters, JSON_PRETTY_PRINT);
                                } catch (\Exception $e) {
                                    echo "Error: " . $e->getMessage();
                                }
                            ?></pre>
                        </div>
                        
                        <div>
                            <strong>Session Data:</strong>
                            <pre>{{ json_encode(session()->all(), JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>