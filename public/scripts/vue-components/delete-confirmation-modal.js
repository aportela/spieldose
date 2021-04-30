const template=function(){return'\n        <div class="modal is-active">\n            <div class="modal-background"></div>\n            <div class="modal-card">\n            <header class="modal-card-head">\n                <p class="modal-card-title"><span class="icon"><i class="fa fa-exclamation-triangle"></i></span> {{ $t("deleteConfirmationModal.labels.modalTitle") }}</p>\n                <button class="delete" aria-label="close" v-on:click.prevent="cancelDelete();"></button>\n            </header>\n            <section class="modal-card-body">\n            {{ $t("deleteConfirmationModal.labels.modalBody") }}\n            </section>\n            <footer class="modal-card-foot">\n                <button class="button is-danger" v-on:click.prevent="confirmDelete();"><span class="icon"><i class="fa fa-check-circle"></i></span><span>{{ $t("deleteConfirmationModal.buttons.ok") }}</span></button>\n                <button class="button" v-on:click.prevent="cancelDelete();"><span class="icon"><i class="fa fa-ban"></i></span><span>{{ $t("deleteConfirmationModal.buttons.cancel") }}</span></button>\n            </footer>\n            </div>\n        </div>\n    '};export default{name:"delete-confirmation-modal",template:'\n        <div class="modal is-active">\n            <div class="modal-background"></div>\n            <div class="modal-card">\n            <header class="modal-card-head">\n                <p class="modal-card-title"><span class="icon"><i class="fa fa-exclamation-triangle"></i></span> {{ $t("deleteConfirmationModal.labels.modalTitle") }}</p>\n                <button class="delete" aria-label="close" v-on:click.prevent="cancelDelete();"></button>\n            </header>\n            <section class="modal-card-body">\n            {{ $t("deleteConfirmationModal.labels.modalBody") }}\n            </section>\n            <footer class="modal-card-foot">\n                <button class="button is-danger" v-on:click.prevent="confirmDelete();"><span class="icon"><i class="fa fa-check-circle"></i></span><span>{{ $t("deleteConfirmationModal.buttons.ok") }}</span></button>\n                <button class="button" v-on:click.prevent="cancelDelete();"><span class="icon"><i class="fa fa-ban"></i></span><span>{{ $t("deleteConfirmationModal.buttons.cancel") }}</span></button>\n            </footer>\n            </div>\n        </div>\n    ',props:["id"],methods:{confirmDelete:function(){this.$emit("confirm-delete",this.id)},cancelDelete:function(){this.$emit("cancel-delete")}}};