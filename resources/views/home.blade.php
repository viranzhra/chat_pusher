@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mb-4 bg-white shadow-sm">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <h5 class="mb-3">Chat dengan Pengguna Lain:</h5>
                    
                    <div class="row {{ count($users) == 2 ? 'justify-content-center' : '' }}">
                        @foreach($users as $user)
                            @if($user->id != auth()->user()->id)
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <h5 class="card-title">{{ $user->name }}</h5>
                                            <a href="{{ route('chat', $user->id) }}" class="btn btn-primary">Chat</a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    @if(count($users) == 1)
                        <div class="mt-3 alert alert-warning text-center" role="alert">
                            Anda satu-satunya pengguna yang terdaftar. Undang teman untuk bergabung!
                        </div>
                    @elseif(count($users) == 0)
                        <div class="mt-3 alert alert-info text-center" role="alert">
                            Tidak ada pengguna lain yang terdaftar untuk chatting. 
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
