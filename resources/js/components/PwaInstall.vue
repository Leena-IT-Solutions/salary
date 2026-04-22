<template>
    <div v-if="!isDismissed && !isStandalone && (deferredPrompt || isInstalled)" class="pwa-install-banner bg-indigo-600 text-white p-3 shadow-lg flex items-center justify-between sticky-top" style="z-index: 9999; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
        <div class="container d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="p-2 bg-white rounded-3 me-3 d-none d-sm-block">
                    <img src="/logo192.png" alt="App Icon" style="width: 32px; height: 32px;">
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">Salary Manager ESS</h6>
                    <small class="opacity-75">{{ isInstalled ? 'Ready on your device' : 'Install for a better experience' }}</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button v-if="!isInstalled" @click="installApp" class="btn btn-light btn-sm fw-bold px-3 rounded-pill">
                    <i class="bi bi-download me-1"></i> Install App
                </button>
                <button v-else @click="openInApp" class="btn btn-light btn-sm fw-bold px-3 rounded-pill">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Open in App
                </button>
                <button @click="dismissBanner" class="btn btn-link text-white p-1 ms-2">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            deferredPrompt: null,
            isInstalled: false,
            isDismissed: false,
            isStandalone: false
        };
    },
    mounted() {
        this.checkInstallation();
        
        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent Chrome 67 and earlier from automatically showing the prompt
            e.preventDefault();
            // Stash the event so it can be triggered later.
            this.deferredPrompt = e;
        });

        window.addEventListener('appinstalled', (evt) => {
            console.log('PWA was installed');
            this.isInstalled = true;
            this.deferredPrompt = null;
        });

        // Check if already dismissed in this session
        if (sessionStorage.getItem('pwa_banner_dismissed')) {
            this.isDismissed = true;
        }

        // Hide if running as standalone
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
            this.isStandalone = true;
        }
    },
    methods: {
        async checkInstallation() {
            if ('getInstalledRelatedApps' in navigator) {
                const relatedApps = await navigator.getInstalledRelatedApps();
                if (relatedApps.length > 0) {
                    this.isInstalled = true;
                }
            }
        },
        async installApp() {
            if (!this.deferredPrompt) return;
            
            this.deferredPrompt.prompt();
            const { outcome } = await this.deferredPrompt.userChoice;
            
            if (outcome === 'accepted') {
                console.log('User accepted the install prompt');
            } else {
                console.log('User dismissed the install prompt');
            }
            this.deferredPrompt = null;
        },
        openInApp() {
            // For PWA, "opening in app" is usually just navigating back to the app URL
            // If the user is already in a browser with the app installed, 
            // some browsers might prompt or just stay.
            // A more advanced way is to use a custom protocol or deep link if Capacitor is used.
            window.location.reload();
        },
        dismissBanner() {
            this.deferredPrompt = null;
            this.isInstalled = false;
            this.isDismissed = true;
            sessionStorage.setItem('pwa_banner_dismissed', 'true');
        }
    }
};
</script>

<style scoped>
.pwa-install-banner {
    border-bottom: 2px solid rgba(255, 255, 255, 0.1);
}
@media (max-width: 576px) {
    .pwa-install-banner h6 {
        font-size: 0.9rem;
    }
    .pwa-install-banner small {
        font-size: 0.75rem;
    }
}
</style>
