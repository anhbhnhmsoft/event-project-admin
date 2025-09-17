@php
    $statePath = $getStatePath();
    $apiKey = $getGoogleMapsApiKey();
    $defaultLocation = $getDefaultLocation();
    $zoom = $getZoom();
    $height = $getHeight();

    // Parse existing value
    $value = $getState();
    $currentData = $value ? json_decode($value, true) : null;

    $currentLat = $currentData['lat'] ?? $defaultLocation['lat'];
    $currentLng = $currentData['lng'] ?? $defaultLocation['lng'];
    $currentAddress = $currentData['address'] ?? '';
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="googleMapsField({
            statePath: '{{ $statePath }}',
            apiKey: '{{ $apiKey }}',
            initialLat: {{ $currentLat }},
            initialLng: {{ $currentLng }},
            initialAddress: '{{ addslashes($currentAddress) }}',
            zoom: {{ $zoom }}
        })"
        x-init="$nextTick(() => initMap())"
        wire:ignore
        class="space-y-3"
    >
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Địa chỉ
            </label>
            <input
                x-ref="addressInput"
                x-model="address"
                @input="handleInputChange"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                placeholder="Nhập địa chỉ để tìm kiếm..."
            />
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Latitude
                </label>
                <input
                    x-model="lat"
                    type="text"
                    readonly
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50"
                />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Longitude
                </label>
                <input
                    x-model="lng"
                    type="text"
                    readonly
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50"
                />
            </div>
        </div>

        <div>
            <div
                x-show="isLoading"
                class="flex items-center justify-center"
                style="height: {{ $height }}px"
            >
                <div class="text-gray-500">
                    <svg class="animate-spin h-8 w-8 mr-2 inline-block" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Đang tải bản đồ...
                </div>
            </div>
            <div
                x-ref="mapContainer"
                x-show="!isLoading"
                style="height: {{ $height }}px"
                class="w-full border border-gray-300 rounded-md"
            ></div>
        </div>

        <input
            x-ref="hiddenInput"
            type="hidden"
            name="{{ $statePath }}"
            x-model="formValue"
        />
    </div>

    <script>
        window.googleMapsLoaded = window.googleMapsLoaded || new Promise((resolve) => {
            if (window.google && window.google.maps) {
                resolve();
                return;
            }
            window.initGoogleMaps = resolve;
        });
    </script>
    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key={{ $apiKey }}&libraries=places&language=vi&callback=initGoogleMaps">
    </script>
    <script>
        // Global Google Maps loader
        window.googleMapsPromise = window.googleMapsPromise || new Promise((resolve) => {
            if (window.google && window.google.maps && window.google.maps.Map) {
                resolve();
                return;
            }

            // Check if script already exists
            const existingScript = document.querySelector('script[src*="maps.googleapis.com"]');
            if (existingScript) {
                const checkLoaded = () => {
                    if (window.google && window.google.maps && window.google.maps.Map) {
                        resolve();
                    } else {
                        setTimeout(checkLoaded, 100);
                    }
                };
                checkLoaded();
                return;
            }

            // Create callback
            window.initGoogleMaps = () => {
                resolve();
            };

            // Load script
            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key={{ $apiKey }}&libraries=places&language=vi&callback=initGoogleMaps`;
            script.async = true;
            script.defer = true;
            script.onerror = () => {
                console.error('Failed to load Google Maps API');
                resolve(); // Resolve anyway to prevent hanging
            };
            document.head.appendChild(script);
        });

        function googleMapsField(config) {
            return {
                map: null,
                marker: null,
                autocomplete: null,
                geocoder: null,
                lat: config.initialLat,
                lng: config.initialLng,
                address: config.initialAddress,
                isLoading: true,
                isInitialized: false,

                init() {
                    // Listen for Livewire updates
                    this.$wire.$on('refresh', () => {
                        this.handleLivewireUpdate();
                    });

                    // Listen for validation errors or form updates
                    document.addEventListener('livewire:update', () => {
                        if (this.isInitialized && !this.map) {
                            this.$nextTick(() => this.initMap());
                        }
                    });
                },

                get formValue() {
                    return JSON.stringify({
                        lat: parseFloat(this.lat) || 0,
                        lng: parseFloat(this.lng) || 0,
                        address: this.address || ''
                    });
                },

                handleInputChange() {
                    this.syncWithLivewire();
                },

                handleLivewireUpdate() {
                    if (this.isInitialized) {
                        this.$nextTick(() => {
                            if (!this.map && this.$refs.mapContainer) {
                                this.initMap();
                            }
                        });
                    }
                },

                async initMap() {
                    // Prevent multiple initializations
                    if (this.isLoading && this.isInitialized) {
                        return;
                    }

                    this.isLoading = true;

                    try {
                        // Wait for Google Maps to load
                        await window.googleMapsPromise;

                        // Double check if Google Maps is available
                        if (!window.google || !window.google.maps || !window.google.maps.Map) {
                            throw new Error('Google Maps API not loaded properly');
                        }

                        // Setup map
                        this.setupMap();
                        this.isLoading = false;
                        this.isInitialized = true;
                    } catch (error) {
                        console.error('Error initializing Google Maps:', error);
                        this.isLoading = false;
                    }
                },

                ensureGoogleMapsLoaded(apiKey) {
                    return new Promise((resolve, reject) => {
                        // Nếu đã load rồi thì resolve luôn
                        if (window.google && window.google.maps && window.google.maps.Map) {
                            resolve();
                            return;
                        }

                        // Kiểm tra xem script đã được thêm chưa
                        const existingScript = document.querySelector('script[src*="maps.googleapis.com"]');
                        if (existingScript) {
                            // Nếu script đã tồn tại, chờ nó load xong
                            existingScript.onload = resolve;
                            existingScript.onerror = reject;
                            return;
                        }

                        // Tạo script mới
                        const script = document.createElement('script');
                        script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places&language=vi&callback=initGoogleMaps`;
                        script.async = true;
                        script.defer = true;

                        // Tạo callback global
                        window.initGoogleMaps = () => {
                            delete window.initGoogleMaps;
                            resolve();
                        };

                        script.onerror = () => {
                            delete window.initGoogleMaps;
                            reject(new Error('Không thể tải Google Maps API'));
                        };

                        document.head.appendChild(script);
                    });
                },

                waitForGoogleMaps() {
                    return new Promise((resolve) => {
                        const checkGoogle = () => {
                            if (window.google && window.google.maps && window.google.maps.Map) {
                                resolve();
                            } else {
                                setTimeout(checkGoogle, 100);
                            }
                        };
                        checkGoogle();
                    });
                },

                setupMap() {
                    // Ensure map container exists
                    if (!this.$refs.mapContainer) {
                        console.error('Map container not found');
                        return;
                    }

                    // Initialize map
                    this.map = new google.maps.Map(this.$refs.mapContainer, {
                        center: {lat: parseFloat(this.lat), lng: parseFloat(this.lng)},
                        zoom: config.zoom,
                        mapTypeControl: true,
                        streetViewControl: true,
                        fullscreenControl: true,
                    });

                    // Initialize marker
                    this.marker = new google.maps.Marker({
                        position: {lat: parseFloat(this.lat), lng: parseFloat(this.lng)},
                        map: this.map,
                        draggable: true,
                        title: 'Kéo marker để thay đổi vị trí'
                    });

                    // Initialize geocoder
                    this.geocoder = new google.maps.Geocoder();

                    // Initialize autocomplete after ensuring addressInput exists
                    this.$nextTick(() => {
                        if (this.$refs.addressInput) {
                            this.autocomplete = new google.maps.places.Autocomplete(
                                this.$refs.addressInput,
                                {
                                    componentRestrictions: {country: 'VN'},
                                    fields: ['address_components', 'formatted_address', 'geometry']
                                }
                            );

                            // Setup event listeners
                            this.setupEventListeners();
                        }
                    });
                },

                setupEventListeners() {
                    // Autocomplete place changed
                    if (this.autocomplete) {
                        this.autocomplete.addListener('place_changed', () => {
                            const place = this.autocomplete.getPlace();
                            if (!place.geometry) return;

                            const location = place.geometry.location;
                            this.updateLocation(
                                location.lat(),
                                location.lng(),
                                place.formatted_address
                            );

                            // Center map on new location
                            this.map.setCenter(location);
                            if (place.geometry.viewport) {
                                this.map.fitBounds(place.geometry.viewport);
                            } else {
                                this.map.setZoom(config.zoom);
                            }
                        });
                    }

                    // Marker drag end
                    if (this.marker) {
                        this.marker.addListener('dragend', () => {
                            const position = this.marker.getPosition();
                            const lat = position.lat();
                            const lng = position.lng();

                            // Update coordinates immediately
                            this.lat = parseFloat(lat).toFixed(6);
                            this.lng = parseFloat(lng).toFixed(6);

                            // Reverse geocode to get address and sync
                            this.reverseGeocode(lat, lng);
                        });
                    }

                    // Map click
                    if (this.map) {
                        this.map.addListener('click', (event) => {
                            const lat = event.latLng.lat();
                            const lng = event.latLng.lng();

                            this.marker.setPosition(event.latLng);
                            this.lat = parseFloat(lat).toFixed(6);
                            this.lng = parseFloat(lng).toFixed(6);

                            // Reverse geocode to get address
                            this.reverseGeocode(lat, lng);
                        });
                    }
                },

                updateLocation(lat, lng, address) {
                    this.lat = parseFloat(lat).toFixed(6);
                    this.lng = parseFloat(lng).toFixed(6);
                    this.address = address || '';

                    if (this.marker) {
                        this.marker.setPosition({lat: parseFloat(lat), lng: parseFloat(lng)});
                    }

                    // Trigger form value update và sync với Livewire
                    this.syncWithLivewire();
                },

                reverseGeocode(lat, lng) {
                    if (!this.geocoder) return;

                    this.geocoder.geocode(
                        {location: {lat: parseFloat(lat), lng: parseFloat(lng)}},
                        (results, status) => {
                            if (status === 'OK' && results[0]) {
                                this.address = results[0].formatted_address;
                                // Sync với Livewire sau khi có địa chỉ
                                this.syncWithLivewire();
                            }
                        }
                    );
                },

                syncWithLivewire() {
                    const value = JSON.stringify({
                        lat: parseFloat(this.lat) || 0,
                        lng: parseFloat(this.lng) || 0,
                        address: this.address || ''
                    });

                    // Update hidden input value
                    this.$refs.hiddenInput.value = value;

                    // Dispatch events to notify form system
                    this.$refs.hiddenInput.dispatchEvent(new Event('input', {bubbles: true}));
                    this.$refs.hiddenInput.dispatchEvent(new Event('change', {bubbles: true}));

                    // Update Livewire if available
                    if (this.$wire && typeof this.$wire.set === 'function') {
                        try {
                            this.$wire.set(config.statePath, value);
                        } catch (e) {
                            console.log('Livewire sync failed:', e);
                        }
                    }
                }
            };
        }
    </script>
</x-dynamic-component>
