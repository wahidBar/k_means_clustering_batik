@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <h3 class="mb-4">
            {{ $user->role === 'partner' ? 'Riwayat Validasi Usaha Anda' : 'Riwayat Validasi Semua UMKM' }}
        </h3>

        @if ($histories->isEmpty())
            <div class="alert alert-info">Belum ada riwayat validasi.</div>
        @else
            <div class="timeline">
                @foreach ($histories as $history)
                    <div class="timeline-item mb-4">
                        <div class="timeline-icon
                                                {{ $history->status === 'Terverifikasi' ? 'bg-success' :
                        ($history->status === 'Tolak' ? 'bg-danger' : 'bg-warning') }}">
                        </div>
                        <div class="timeline-content p-4 shadow-sm rounded-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">
                                    {{ optional($history->updated_at)
                        ? \Carbon\Carbon::parse($history->updated_at)
                            ->setTimezone('Asia/Jakarta')
                            ->translatedFormat('d F Y, H:i')
                        : '-' }}
                                </small>

                                <span class="badge
                                                        {{ $history->status === 'Terverifikasi' ? 'bg-success' :
                        ($history->status === 'Tolak' ? 'bg-danger' : 'bg-secondary') }}">
                                    {{ ucfirst($history->status) }}
                                </span>
                            </div>

                            <h5 class="fw-bold mb-2">
                                {{-- @if($user->role_id === 1) --}}
                                UMKM - {{ $history->partner->business_name ?? '-' }}
                                {{-- @else
                                    Validasi Usaha Anda
                                @endif --}}
                            </h5>

                            <p class="mb-2">
                                NOTE: {!! nl2br(e($history->note ?? 'Tidak ada catatan.')) !!}
                            </p>

                            <small class="text-muted">
                                Divalidasi oleh: <strong>{{ $history->user->name ?? '-' }}</strong>
                            </small>
                        </div>
                    </div>
                @endforeach
            </div>
            {{ $histories->links() }}

        @endif
    </div>

    {{-- Style timeline --}}
    <style>
        .timeline {
            position: relative;
            margin-left: 20px;
            border-left: 3px solid #dee2e6;
            padding-left: 25px;
        }

        .timeline-item {
            position: relative;
        }

        .timeline-icon {
            position: absolute;
            left: -11px;
            top: 5px;
            width: 15px;
            height: 15px;
            border-radius: 50%;
        }

        .bg-success {
            background-color: #28a745 !important;
        }

        .bg-danger {
            background-color: #dc3545 !important;
        }

        .bg-warning {
            background-color: #ffc107 !important;
        }

        .bg-secondary {
            background-color: #6c757d !important;
        }

        .timeline-content {
            background: #fff;
            border: 1px solid #e9ecef;
        }
    </style>
@endsection
