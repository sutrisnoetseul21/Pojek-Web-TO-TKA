<div>
    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
        Daftar ujian yang ditugaskan kepada <strong>{{ $record->nama_lengkap }}</strong>.
        <br>
        <span class="text-warning-600 dark:text-warning-400 font-medium flex items-center gap-1 mt-1">
            💡 Untuk mengedit data ini, silakan ke menu <strong>Pengaturan Server Proktor</strong>.
        </span>
    </p>

    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                <tr>
                    <th class="px-4 py-3">Paket Ujian</th>
                    <th class="px-4 py-3">Sesi</th>
                    <th class="px-4 py-3">Ruangan</th>
                    <th class="px-4 py-3">Kelas</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($record->penugasanRuangan as $penugasan)
                    <tr class="bg-white border-b dark:bg-gray-900 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                            {{ $penugasan->jadwalTryout->paketTryout->nama_paket ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $penugasan->jadwalTryout->nama_sesi ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $penugasan->ruangan->nama_ruangan ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $penugasan->kelas->nama_kelas ?? 'Semua Kelas' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-gray-400">
                            Belum ada penugasan ujian.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
