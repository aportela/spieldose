const template = function () {
    return `
        <div class="modal is-active">
            <div class="modal-background"></div>
            <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title"><span class="icon"><i class="fa fa-exclamation-triangle"></i></span> {{ $t("deleteConfirmationModal.labels.modalTitle") }}</p>
                <button class="delete" aria-label="close" v-on:click.prevent="cancelDelete();"></button>
            </header>
            <section class="modal-card-body">
            {{ $t("deleteConfirmationModal.labels.modalBody") }}
            </section>
            <footer class="modal-card-foot">
                <button class="button is-danger" v-on:click.prevent="confirmDelete();"><span class="icon"><i class="fa fa-check-circle"></i></span><span>{{ $t("deleteConfirmationModal.buttons.ok") }}</span></button>
                <button class="button" v-on:click.prevent="cancelDelete();"><span class="icon"><i class="fa fa-ban"></i></span><span>{{ $t("deleteConfirmationModal.buttons.cancel") }}</span></button>
            </footer>
            </div>
        </div>
    `;
};

export default {
    name: 'delete-confirmation-modal',

    template: template(),
    props: ['id'],
    methods: {
        confirmDelete: function () {
            this.$emit('confirm-delete', this.id);
        },
        cancelDelete: function () {
            this.$emit('cancel-delete');
        }
    }
}