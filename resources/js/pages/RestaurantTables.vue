<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useToast } from '@/components/ui/toast';
import ToastProvider from '@/components/ui/toast/ToastProvider.vue';
import axios from 'axios';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Restaurant Tables',
        href: '/resto-tables',
    },
];

// Get initial data from props
const props = defineProps<{
    emptyTablesCount?: number,
    busyTablesCount?: number,
    emptyTables?: Array<{
        table_name: string,
        seat_capacity: number
    }>
}>();

const emptyTablesCount = ref(props.emptyTablesCount || 0);
const busyTablesCount = ref(props.busyTablesCount || 0);
const emptyTables = ref(props.emptyTables || []);
const busyTables = ref<Array<{
    table_name: string,
    seat_capacity: number,
    guest_count: number
}>>([]);
const lastUpdated = ref(new Date().toLocaleTimeString());
let pollingInterval: number | null = null;

// Table assignment modal state
const isAssignTableModalOpen = ref(false);
const selectedTable = ref('');
const guestCount = ref(1);
const isSubmitting = ref(false);
const { toast } = useToast();

// Table release modal state
const isReleaseTableModalOpen = ref(false);
const selectedBusyTable = ref('');

// Computed property to get selected table capacity
const selectedTableCapacity = computed(() => {
    const table = emptyTables.value.find(t => t.table_name === selectedTable.value);
    return table?.seat_capacity || 0;
});

// Function to fetch latest table data
const fetchTableData = async () => {
    try {
        // Fetch counts
        const countsResponse = await fetch('/api/all-table-counts');
        const countsData = await countsResponse.json();
        emptyTablesCount.value = countsData.emptyTablesCount;
        busyTablesCount.value = countsData.busyTablesCount;

        // Fetch empty tables
        const emptyTablesResponse = await fetch('/api/empty-tables');
        const emptyTablesData = await emptyTablesResponse.json();
        emptyTables.value = emptyTablesData.emptyTables;

        // Fetch busy tables
        const busyTablesResponse = await fetch('/api/busy-tables');
        const busyTablesData = await busyTablesResponse.json();
        busyTables.value = busyTablesData.busyTables || [];

        lastUpdated.value = new Date().toLocaleTimeString();
    } catch (error) {
        console.error('Error fetching table data:', error);
    }
};

// Open the assign table modal
const openAssignTableModal = () => {
    // Reset form data
    selectedTable.value = emptyTables.value.length > 0 ? emptyTables.value[0].table_name : '';
    guestCount.value = 1;
    isAssignTableModalOpen.value = true;
};

// Close the assign table modal
const closeAssignTableModal = () => {
    isAssignTableModalOpen.value = false;
};

// Open the release table modal
const openReleaseTableModal = () => {
    fetchTableData(); // Make sure we have the latest data
    // Reset form data
    if (busyTables.value.length > 0) {
        selectedBusyTable.value = busyTables.value[0].table_name;
        isReleaseTableModalOpen.value = true;
    } else {
        toast('No busy tables to release', { variant: 'destructive' });
    }
};

// Close the release table modal
const closeReleaseTableModal = () => {
    isReleaseTableModalOpen.value = false;
};

// Assign table function
const assignTable = async () => {
    // Validate guest count
    if (guestCount.value <= 0) {
        toast('Guest count must be at least 1', { variant: 'destructive' });
        return;
    }

    if (guestCount.value > selectedTableCapacity.value) {
        toast(`This table can only accommodate ${selectedTableCapacity.value} guests`, { variant: 'destructive' });
        return;
    }

    isSubmitting.value = true;

    try {
        // Using axios instead of fetch (Laravel includes axios by default)
        const response = await axios.post('/api/assign-table', {
            tableName: selectedTable.value,
            guestCount: guestCount.value
        });

        // Success - update UI
        emptyTablesCount.value = response.data.emptyTablesCount;
        busyTablesCount.value = response.data.busyTablesCount;
        emptyTables.value = response.data.emptyTables;
        if (response.data.busyTables) {
            busyTables.value = response.data.busyTables;
        }
        lastUpdated.value = new Date().toLocaleTimeString();

        // Show success message
        toast('Table assigned successfully!', { variant: 'default' });

        // Close modal
        closeAssignTableModal();

        // Refresh data without full page reload
        fetchTableData();
    } catch (error) {
        console.error('Error assigning table:', error);
        toast('An error occurred while assigning the table', { variant: 'destructive' });
    } finally {
        isSubmitting.value = false;
    }
};

// Release table function
const releaseTable = async () => {
    if (!selectedBusyTable.value) {
        toast('Please select a table to release', { variant: 'destructive' });
        return;
    }

    isSubmitting.value = true;

    try {
        // Using axios to make the release request
        const response = await axios.post('/api/release-table', {
            tableName: selectedBusyTable.value
        });

        // Success - update UI
        emptyTablesCount.value = response.data.emptyTablesCount;
        busyTablesCount.value = response.data.busyTablesCount;
        emptyTables.value = response.data.emptyTables;
        if (response.data.busyTables) {
            busyTables.value = response.data.busyTables;
        }
        lastUpdated.value = new Date().toLocaleTimeString();

        // Show success message
        toast('Table released successfully!', { variant: 'default' });

        // Close modal
        closeReleaseTableModal();

        // Refresh data without full page reload
        fetchTableData();
    } catch (error) {
        console.error('Error releasing table:', error);
        toast('An error occurred while releasing the table', { variant: 'destructive' });
    } finally {
        isSubmitting.value = false;
    }
};

// Setup polling on component mount
onMounted(() => {
    // Immediately fetch data to ensure we have the latest
    fetchTableData();

    // Set up polling every 30 seconds
    pollingInterval = window.setInterval(fetchTableData, 30000);
});

// Cleanup on component unmount
onUnmounted(() => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});
</script>

<template>
    <Head title="Restaurant Tables" />
    <ToastProvider />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <!-- Main content card -->
            <div class="relative flex-1 rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                <h1 class="text-2xl font-bold mb-4">Restaurant Tables</h1>
                <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 shadow-sm border border-sidebar-border/50">
                    <div class="space-y-4">
                        <!-- Available tables component -->
                        <p class="text-xl font-medium text-green-600 dark:text-green-400">
                            {{ emptyTablesCount }} tables are available.
                        </p>

                        <!-- Busy tables component -->
                        <p class="text-xl font-medium text-yellow-500 dark:text-yellow-400">
                            {{ busyTablesCount }} tables are currently busy.
                        </p>

                        <!-- Last updated timestamp -->
                        <p class="text-sm text-gray-500 mt-2">
                            Last updated: {{ lastUpdated }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Additional cards for functionality -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <!-- Seat guests card -->
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border bg-white dark:bg-neutral-800">
                    <h2 class="text-lg font-semibold mb-4">Seat waiting guests</h2>
                    <button
                        @click="openAssignTableModal"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium transition-colors"
                        :disabled="emptyTablesCount === 0"
                    >
                        Assign Table
                    </button>
                    <p v-if="emptyTablesCount === 0" class="text-sm text-red-500 mt-2">
                        No tables available for assignment
                    </p>
                </div>

                <!-- Release table card -->
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border bg-white dark:bg-neutral-800">
                    <h2 class="text-lg font-semibold mb-4">Release guest table</h2>
                    <button
                        @click="openReleaseTableModal"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md font-medium transition-colors"
                        :disabled="busyTablesCount === 0"
                    >
                        Clear assigned table
                    </button>
                    <p v-if="busyTablesCount === 0" class="text-sm text-red-500 mt-2">
                        No busy tables to release
                    </p>
                </div>

                <!-- Third placeholder card -->
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
            </div>
        </div>

        <!-- Table Assignment Modal -->
        <div v-if="isAssignTableModalOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 shadow-xl max-w-md w-full mx-4">
                <h2 class="text-xl font-bold mb-4">Assign Table to Guests</h2>

                <form @submit.prevent="assignTable" class="space-y-4">
                    <!-- Table selection -->
                    <div>
                        <label for="table-select" class="block text-sm font-medium mb-1">Select Table</label>
                        <select
                            id="table-select"
                            v-model="selectedTable"
                            class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-neutral-900"
                            required
                        >
                            <option v-for="table in emptyTables" :key="table.table_name" :value="table.table_name">
                                {{ table.table_name }} (Capacity: {{ table.seat_capacity }})
                            </option>
                        </select>
                    </div>

                    <!-- Guest count -->
                    <div>
                        <label for="guest-count" class="block text-sm font-medium mb-1">Number of Guests</label>
                        <input
                            id="guest-count"
                            type="number"
                            v-model="guestCount"
                            min="1"
                            :max="selectedTableCapacity"
                            class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-neutral-900"
                            required
                        />
                        <p class="text-xs text-gray-500 mt-1">Max capacity: {{ selectedTableCapacity }} guests</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3 pt-2">
                        <button
                            type="button"
                            @click="closeAssignTableModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md"
                            :disabled="isSubmitting"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium transition-colors disabled:opacity-50"
                            :disabled="isSubmitting"
                        >
                            {{ isSubmitting ? 'Assigning...' : 'Confirm' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Release Modal -->
        <div v-if="isReleaseTableModalOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 shadow-xl max-w-md w-full mx-4">
                <h2 class="text-xl font-bold mb-4">Release Occupied Table</h2>

                <form @submit.prevent="releaseTable" class="space-y-4">
                    <!-- Table selection -->
                    <div>
                        <label for="busy-table-select" class="block text-sm font-medium mb-1">Select Table to Release</label>
                        <select
                            id="busy-table-select"
                            v-model="selectedBusyTable"
                            class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-neutral-900"
                            required
                        >
                            <option v-for="table in busyTables" :key="table.table_name" :value="table.table_name">
                                {{ table.table_name }} (Currently: {{ table.guest_count }} guests)
                            </option>
                        </select>
                    </div>

                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        This will mark the selected table as empty and available for new guests.
                    </p>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3 pt-2">
                        <button
                            type="button"
                            @click="closeReleaseTableModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md"
                            :disabled="isSubmitting"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md font-medium transition-colors disabled:opacity-50"
                            :disabled="isSubmitting"
                        >
                            {{ isSubmitting ? 'Releasing...' : 'Confirm' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
