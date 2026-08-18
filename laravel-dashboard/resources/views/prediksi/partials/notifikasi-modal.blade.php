@php
    $hasSuccess = session('success');
    $hasErrors = $errors->any();
    $hasGagalPrediksi = session('gagal_prediksi');

    $showModal = $hasSuccess || $hasErrors || $hasGagalPrediksi;
@endphp

@if($showModal)
    <div id="notifikasiModal"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">

        <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl overflow-hidden">

            {{-- HEADER --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">
                        Informasi Proses
                    </h3>
                    <p class="text-sm text-gray-500">
                        Status terbaru dari sistem prediksi.
                    </p>
                </div>

                <button type="button"
                        onclick="closeNotifikasiModal()"
                        class="w-9 h-9 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- BODY --}}
            <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">

                {{-- SUCCESS --}}
                @if($hasSuccess)
                    <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-full bg-green-100 text-green-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-check"></i>
                            </div>

                            <div>
                                <h4 class="font-semibold text-green-700 mb-1">
                                    Berhasil
                                </h4>
                                <p class="text-sm text-green-700 leading-relaxed">
                                    {{ session('success') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ERROR VALIDASI / ERROR SISTEM --}}
                @if($hasErrors)
                    <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>

                            <div>
                                <h4 class="font-semibold text-red-700 mb-2">
                                    Terjadi Kesalahan
                                </h4>

                                <ul class="list-disc list-inside space-y-1 text-sm text-red-700">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- CATATAN GAGAL PREDIKSI --}}
                @if($hasGagalPrediksi)
                    <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-circle-info"></i>
                            </div>

                            <div>
                                <h4 class="font-semibold text-yellow-700 mb-2">
                                    Catatan Proses Prediksi
                                </h4>

                                <ul class="list-disc list-inside space-y-1 text-sm text-yellow-700">
                                    @foreach(session('gagal_prediksi') as $gagal)
                                        <li>{{ $gagal }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    <script>
        function closeNotifikasiModal() {
            const modal = document.getElementById('notifikasiModal');

            if (modal) {
                modal.classList.add('hidden');
            }
        }
    </script>
@endif