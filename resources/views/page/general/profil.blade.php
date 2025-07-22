@extends('layouts.home', ['title' => 'Lihat Profil'])

@push('style')
    <style>
        .img {
            width: 200px;
            height: 200px;
            overflow: hidden;
        }

        .img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
@endpush

@section('content')
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="card shadow-lg w-100" style="max-width: 700px;">
            <div class="card-header bg-primary text-white text-center rounded-top">
                <h4 class="card-title mb-0">Profil Saya</h4>
            </div>
            <form action="{{ route('profil.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="card-body p-4">
                    <div class="d-flex flex-column align-items-center mb-4">
                        <div class="img mb-2" style="width: 140px; height: 140px; border-radius: 50%; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            @if ($user->photo)
                                <img id="preview-image" src="{{ asset('user/photo/' . $user->photo) }}" alt="Profile"
                                    class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <img id="preview-image" src="{{ asset('assets/img/profile.jpg') }}" alt="Profile"
                                    class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                            @endif
                        </div>
                        <input type="file" id="image-input" class="form-control d-none" accept="image/*" name="photo">
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2 px-4"
                            onclick="document.getElementById('image-input').click();">
                            Ubah Foto
                        </button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label">Nama</label>
                                <input type="text" class="form-control" placeholder="Masukkan Nama"
                                    name="name" value="{{ old('name', $user->name) }}">
                                @error('name')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <label class="form-label">No Whatsapp</label>
                                <input type="text" class="form-control" placeholder="Masukkan No Whatsapp"
                                    name="no_phone" value="{{ old('no_phone', $user->no_phone ?? '08XXXXXXX') }}">
                                @error('no_phone')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" placeholder="Masukkan Email"
                                    name="email" value="{{ old('email', $user->email) }}">
                                @error('email')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            @php
                                $role = [
                                    'cfo' => 'Chief Financial Officer',
                                    'ceo' => 'Chief Executive Officer',
                                ];
                            @endphp
                            <div class="mb-2">
                                <label class="form-label">Bagian</label>
                                <input type="text" class="form-control bg-light" value="{{ $role[$user->role] ?? '-' }}"
                                    readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control"
                                placeholder="Masukkan Password jika ingin mengganti password" name="password"
                                value="{{ old('password') }}">
                            @error('password')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center bg-white border-0 pb-4">
                    <button type="submit" class="btn btn-primary px-5 py-2 shadow">Update</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
    <script>
        const imageInput = document.getElementById('image-input');
        const previewImage = document.getElementById('preview-image');

        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
@endpush
