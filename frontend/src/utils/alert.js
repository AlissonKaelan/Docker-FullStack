import Swal from 'sweetalert2';

// 1. Notificação "Toast" (Aquelas que aparecem no canto e somem sozinhas)
// Ideal para: "Salvo com sucesso", "Item excluído", "Erro ao carregar".
export const notify = (icon, title) => {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end', // Canto superior direito
        showConfirmButton: false,
        timer: 3000, // Some em 3 segundos
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    Toast.fire({
        icon: icon, // 'success', 'error', 'warning', 'info'
        title: title
    });
};

// 2. Confirmação (Modal de Pergunta)
// Ideal para: Deletar itens ou ações irreversíveis.
export const confirmAction = async (title, text, confirmButtonText = 'Sim, excluir!') => {
    const result = await Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444', // Vermelho para perigo
        cancelButtonColor: '#6c757d', // Cinza para cancelar
        confirmButtonText: confirmButtonText,
        cancelButtonText: 'Cancelar'
    });

    return result.isConfirmed; // Retorna true ou false
};