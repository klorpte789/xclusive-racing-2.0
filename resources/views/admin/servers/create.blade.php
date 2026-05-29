@extends('layouts.admin')

@section('title', 'Add FTP Server')
@section('page-title', 'Add FTP Server')

@section('page-actions')
    <a href="{{ route('admin.servers.index') }}" class="btn btn-sm btn-outline-secondary fw-bold text-uppercase" style="font-size:.78rem">
        ← Back
    </a>
@endsection

@section('content')

<div class="d-flex justify-content-center">
<div class="admin-form-card w-100" style="max-width:640px">
    <div class="fw-black text-uppercase fst-italic text-dark mb-4" style="font-size:1.05rem">New GPortal Server</div>

    <form action="{{ route('admin.servers.store') }}" method="POST">
        @csrf

        <div class="row g-3 mb-3">
            <div class="col-12">
                <label class="form-label fw-bold text-uppercase text-dark" style="font-size:.72rem;letter-spacing:.06em">Server Name</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="form-control @error('name') is-invalid @enderror"
                       style="border-color:#e5e7eb;font-size:.875rem"
                       required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-9">
                <label class="form-label fw-bold text-uppercase text-dark" style="font-size:.72rem;letter-spacing:.06em">IP Address / FTP Link</label>
                <input type="text" name="host" value="{{ old('host') }}"
                       class="form-control @error('host') is-invalid @enderror"
                       style="border-color:#e5e7eb;font-family:monospace;font-size:.875rem"
                       required>
                <div class="form-text" style="font-size:.72rem;color:#9ca3af">Het IP-adres of de FTP Link zoals GPortal die toont, zonder ftp://</div>
                @error('host')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold text-uppercase text-dark" style="font-size:.72rem;letter-spacing:.06em">Port</label>
                <input type="number" name="port" value="{{ old('port', 21) }}"
                       class="form-control @error('port') is-invalid @enderror"
                       style="border-color:#e5e7eb;font-size:.875rem"
                       required>
                @error('port')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label fw-bold text-uppercase text-dark" style="font-size:.72rem;letter-spacing:.06em">FTP Username</label>
                <input type="text" name="username" value="{{ old('username') }}"
                       class="form-control @error('username') is-invalid @enderror"
                       autocomplete="off"
                       style="border-color:#e5e7eb;font-size:.875rem"
                       required>
                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold text-uppercase text-dark" style="font-size:.72rem;letter-spacing:.06em">FTP Password</label>
                <input type="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       autocomplete="new-password"
                       style="border-color:#e5e7eb;font-size:.875rem"
                       required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label fw-bold text-uppercase text-dark" style="font-size:.72rem;letter-spacing:.06em">Results Path</label>
            <input type="text" name="path" value="{{ old('path', '/results') }}"
                   class="form-control @error('path') is-invalid @enderror"
                   style="border-color:#e5e7eb;font-family:monospace;font-size:.875rem"
                   required>
            <div class="form-text" style="font-size:.72rem;color:#9ca3af">Directory on the GPortal server where JSON result files are saved.</div>
            @error('path')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex gap-3">
            <button type="submit" class="btn fw-black text-uppercase text-white px-4"
                    style="background:#7c3aed;height:42px;font-size:.82rem">
                Add Server
            </button>
            <a href="{{ route('admin.servers.index') }}" class="btn btn-outline-secondary fw-bold text-uppercase"
               style="height:42px;font-size:.82rem">
                Cancel
            </a>
        </div>
    </form>
</div>
</div>

@endsection