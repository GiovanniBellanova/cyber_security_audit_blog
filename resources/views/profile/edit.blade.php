<x-layout>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8">
                <h1 class="display-6 text-center my-5">Modifica Profilo</h1>
                
                <div class="card p-5 shadow">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nome:</label>
                            <input type="text" name="name" class="form-control" value="{{ Auth::user()->name }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email:</label>
                            <input type="email" name="email" class="form-control" value="{{ Auth::user()->email }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nuova Password:</label>
                            <input type="password" name="password" class="form-control" placeholder="Lascia vuoto se non vuoi cambiarla">
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">Salva Modifiche</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>