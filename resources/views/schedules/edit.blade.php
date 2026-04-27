@extends('layouts.app')

@section('title', 'Editar Agenda')

@section('content')

<div style="max-width:600px;">

    <div class="page-header" style="margin-bottom:20px;">
        <div style="display:flex; align-items:center; gap:8px;">
            <a href="{{ route('schedules.show', $schedule) }}" class="breadcrumb-back">
                <span class="material-symbols-rounded">arrow_back</span>
            </a>
            <div>
                <h1 class="page-title">Editar Agenda</h1>
                <div class="page-title-sub">{{ $schedule->name }}</div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('schedules.update', $schedule) }}" class="card">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="form-label">
                <span class="material-symbols-rounded">edit</span>
                Nome *
            </label>
            <input type="text" name="name" value="{{ old('name', $schedule->name) }}" required
                   class="form-input" placeholder="Ex: Consultório Dr. João">
            @error('name') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">
                <span class="material-symbols-rounded">description</span>
                Descrição
            </label>
            <textarea name="description" rows="3"
                      class="form-input" placeholder="Descreva sua agenda (opcional)">{{ old('description', $schedule->description) }}</textarea>
        </div>

        <div class="form-group">
            <label class="form-label">
                <span class="material-symbols-rounded">timer</span>
                Duração do slot
            </label>
            <select name="slot_duration" class="form-input">
                @foreach([15, 30, 45, 60, 90, 120] as $min)
                    <option value="{{ $min }}" {{ old('slot_duration', $schedule->slot_duration) == $min ? 'selected' : '' }}>
                        {{ $min }} minutos
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group form-group--check">
            <label class="check-label">
                <input type="checkbox" name="is_public" value="1"
                       {{ old('is_public', $schedule->is_public) ? 'checked' : '' }} class="check-input">
                <div class="check-body">
                    <span class="material-symbols-rounded check-icon">public</span>
                    <div>
                        <div style="font-size:14px; font-weight:500; color:#1e1e2e;">Agenda pública</div>
                        <div style="font-size:12px; color:#6c7086;">Permite agendamento via link público compartilhável</div>
                    </div>
                </div>
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">
                <span class="material-symbols-rounded">save</span>
                Salvar alterações
            </button>
            <a href="{{ route('schedules.show', $schedule) }}" class="btn-cancel">
                Cancelar
            </a>
        </div>

        {{-- Zona de perigo --}}
        <div style="margin-top:28px; padding-top:20px; border-top:1px solid #fee2e2;">
            <div style="font-size:11px; font-weight:700; color:#ef4444; text-transform:uppercase; letter-spacing:.05em; margin-bottom:10px;">
                Zona de perigo
            </div>
            <form method="POST" action="{{ route('schedules.destroy', $schedule) }}"
                  onsubmit="return confirm('Tem certeza? Esta ação não pode ser desfeita.')"
                  style="display:inline;">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger">
                    <span class="material-symbols-rounded">delete</span>
                    Excluir agenda
                </button>
            </form>
        </div>

    </form>
</div>

<style>
.breadcrumb-back {
    color: #6c7086; text-decoration: none; display: flex; align-items: center;
    padding: 4px; border-radius: 6px; transition: background .15s, color .15s;
}
.breadcrumb-back:hover { background: #f1f5f9; color: #1e1e2e; }
.breadcrumb-back .material-symbols-rounded { font-size: 20px; }

.form-group { margin-bottom: 18px; }
.form-label {
    display: flex; align-items: center; gap: 6px;
    font-size: 13px; font-weight: 600; margin-bottom: 7px; color: #374151;
}
.form-label .material-symbols-rounded { font-size: 15px; color: #7c3aed; }
.form-input {
    width: 100%; padding: 10px 12px; border: 1.5px solid #e5e7eb;
    border-radius: 8px; font-size: 14px; outline: none;
    transition: border-color .2s; background: #fff; font-family: inherit;
    resize: vertical;
}
.form-input:focus { border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,.08); }
.form-error { color: #ef4444; font-size: 12px; margin-top: 4px; }

.form-group--check { margin-bottom: 24px; }
.check-label { display: flex; align-items: flex-start; gap: 12px; cursor: pointer; }
.check-input { width: 18px; height: 18px; margin-top: 2px; accent-color: #7c3aed; flex-shrink: 0; }
.check-body { display: flex; align-items: center; gap: 10px; }
.check-icon {
    font-size: 20px; color: #7c3aed;
    font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 24;
}

.form-actions { display: flex; gap: 12px; flex-wrap: wrap; }
.btn-submit {
    display: inline-flex; align-items: center; gap: 8px;
    background: #7c3aed; color: #fff; padding: 11px 22px;
    border-radius: 8px; border: none; cursor: pointer;
    font-size: 14px; font-weight: 600; transition: background .15s;
}
.btn-submit:hover { background: #6d28d9; }
.btn-submit .material-symbols-rounded { font-size: 18px; }
.btn-cancel {
    display: inline-flex; align-items: center;
    background: #f3f4f6; color: #374151; padding: 11px 22px;
    border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600;
    transition: background .15s;
}
.btn-cancel:hover { background: #e5e7eb; }
.btn-danger {
    display: inline-flex; align-items: center; gap: 8px;
    background: transparent; border: 1.5px solid #fca5a5; color: #ef4444;
    padding: 9px 18px; border-radius: 8px; cursor: pointer;
    font-size: 13px; font-weight: 500; transition: background .15s, border-color .15s;
}
.btn-danger:hover { background: #fee2e2; border-color: #ef4444; }
.btn-danger .material-symbols-rounded { font-size: 16px; }

@media (max-width: 480px) {
    .form-actions { flex-direction: column; }
    .btn-submit, .btn-cancel { width: 100%; justify-content: center; }
}
</style>

@endsection
