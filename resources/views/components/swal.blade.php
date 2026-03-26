@props([
    'successKey' => 'success',
    'errorKey' => 'error'
])

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // 1️⃣ Alertas de sessão
    @if(session($successKey))
        Swal.fire({
            icon: 'success',
            title: 'Sucesso',
            text: "{{ session($successKey) }}",
            background: '#111',
            color: '#fff',
            confirmButtonColor: '#e50914'
        });
    @endif

    @if(session($errorKey))
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: "{{ session($errorKey) }}",
            background: '#111',
            color: '#fff',
            confirmButtonColor: '#e50914'
        });
    @endif

    // 2️⃣ Confirmação de delete
    document.querySelectorAll('.swal-delete').forEach(button => {
        button.addEventListener('click', function () {
            const form = button.closest('form');
            Swal.fire({
                title: 'Tem certeza que deseja apagar este filme?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e50914',
                cancelButtonColor: '#444',
                confirmButtonText: 'Sim, apagar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if(result.isConfirmed){
                    form.submit();
                }
            });
        });
    });
</script>