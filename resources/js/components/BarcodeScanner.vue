<template>
    <div class="barcode-scanner">
        <!-- Trigger Button -->
        <button 
            type="button"
            @click="openScanner" 
            class="btn-secondary flex items-center gap-2"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
            </svg>
            Scan
        </button>

        <!-- Scanner Modal -->
        <Teleport to="body">
            <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70">
                <div class="bg-white dark:bg-dark-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-fade-in">
                    <!-- Header -->
                    <div class="flex items-center justify-between p-4 border-b border-dark-200 dark:border-dark-700">
                        <h3 class="text-lg font-bold text-dark-900 dark:text-white">Scan Barcode</h3>
                        <button 
                            @click="closeScanner" 
                            class="p-2 rounded-lg hover:bg-dark-100 dark:hover:bg-dark-700 transition-colors"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Scanner Area -->
                    <div class="p-4">
                        <div 
                            id="barcode-reader" 
                            class="w-full rounded-xl overflow-hidden bg-dark-100 dark:bg-dark-700"
                        ></div>
                        
                        <p class="text-sm text-dark-500 text-center mt-4">
                            Arahkan kamera ke barcode produk
                        </p>

                        <!-- Error Message -->
                        <div v-if="error" class="mt-4 p-3 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg text-sm text-center">
                            {{ error }}
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-4 border-t border-dark-200 dark:border-dark-700">
                        <button 
                            @click="closeScanner" 
                            class="btn-ghost w-full"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, onBeforeUnmount } from 'vue';
import { Html5Qrcode } from 'html5-qrcode';

const emit = defineEmits(['scanned']);

const isOpen = ref(false);
const error = ref(null);
let html5QrCode = null;

const openScanner = async () => {
    isOpen.value = true;
    error.value = null;

    // Wait for DOM to render
    await new Promise(resolve => setTimeout(resolve, 100));

    try {
        html5QrCode = new Html5Qrcode("barcode-reader");

        const config = {
            fps: 10,
            qrbox: { width: 250, height: 150 },
            aspectRatio: 1.5,
            formatsToSupport: [
                0,  // QR_CODE
                1,  // AZTEC
                2,  // CODABAR
                3,  // CODE_39
                4,  // CODE_93
                5,  // CODE_128
                6,  // DATA_MATRIX
                7,  // MAXICODE
                8,  // ITF
                9,  // EAN_13
                10, // EAN_8
                11, // PDF_417
                12, // RSS_14
                13, // RSS_EXPANDED
                14, // UPC_A
                15, // UPC_E
                16  // UPC_EAN_EXTENSION
            ]
        };

        await html5QrCode.start(
            { facingMode: "environment" },
            config,
            onScanSuccess,
            onScanFailure
        );
    } catch (err) {
        console.error('Camera error:', err);
        error.value = 'Tidak dapat mengakses kamera. Pastikan izin kamera sudah diberikan.';
    }
};

const onScanSuccess = (decodedText, decodedResult) => {
    // Emit the scanned barcode
    emit('scanned', decodedText);
    
    // Close scanner after successful scan
    closeScanner();
};

const onScanFailure = (errorMessage) => {
    // Ignore scan failures (normal when no barcode is in view)
};

const closeScanner = async () => {
    if (html5QrCode) {
        try {
            await html5QrCode.stop();
            html5QrCode.clear();
        } catch (err) {
            console.error('Error stopping scanner:', err);
        }
        html5QrCode = null;
    }
    isOpen.value = false;
    error.value = null;
};

// Cleanup on component unmount
onBeforeUnmount(() => {
    if (html5QrCode) {
        html5QrCode.stop().catch(() => {});
    }
});
</script>

<style scoped>
#barcode-reader {
    min-height: 300px;
}

:deep(#barcode-reader video) {
    border-radius: 0.75rem;
}

:deep(#barcode-reader__scan_region) {
    background: transparent !important;
}

:deep(#barcode-reader__dashboard_section_csr) {
    display: none !important;
}

:deep(#barcode-reader__dashboard_section_swaplink) {
    display: none !important;
}

.animate-fade-in {
    animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
</style>
