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
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title">
                        Profil Saya
                    </h4>
                </div>
            </div>
            <form action="{{ route('profil.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="row p-5">
                        <div class="col-md-2">
                            <div class="img">
                                @if ($user->photo)
                                    <img id="preview-image" src="{{ asset('user/photo/' . $user->photo) }}" alt="Profile"
                                        class="img-fluid rounded" style="max-width: 250px;">
                                @else
                                    <img id="preview-image" src="{{ asset('assets/img/profile.jpg') }}" alt="Profile"
                                        class="img-fluid rounded" style="max-width: 250px;">
                                @endif
                            </div>
                            <div class="mt-3">
                                <input type="file" id="image-input" class="form-control d-none" accept="image/*"
                                    name="photo">
                                <button type="button" class="btn btn-secondary mt-2"
                                    onclick="document.getElementById('image-input').click();">
                                    Ubah Foto
                                </button>
                            </div>
                        </div>

                        <div class="col-md-10">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nama</label>
                                        <input type="text" class="form-control" placeholder="Masukkan Nama"
                                            name="name" value="{{ old('name', $user->name) }}">
                                    </div>
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <div class="mb-3">
                                        <label class="form-label">No Whatsapp</label>
                                        <input type="text" class="form-control" placeholder="Masukkan No Whatsapp"
                                            name="no_phone" value="{{ old('no_phone', $user->no_phone ?? '08XXXXXXX') }}">
                                    </div>
                                    @error('no_phone')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                @php
                                    $role = [
                                        'cfo' => 'Chief Financial Officer',
                                        'ceo' => 'Chief Executive Officer',
                                    ];
                                @endphp

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" placeholder="Masukkan Email"
                                            name="email" value="{{ old('email', $user->email) }}">
                                    </div>
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <div class="mb-3">
                                        <label class="form-label">Bagian</label>
                                        <input type="text" class="form-control" value="{{ $role[$user->role] ?? '-' }}"
                                            readonly>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" class="form-control"
                                        placeholder="Masukkan Password jika ingin mengganti password" name="password"
                                        value="{{ old('password') }}">
                                </div>
                                @error('password')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end p-3">
                    <button type="submit" class="btn btn-primary">Update</button>
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
