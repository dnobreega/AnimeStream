<!-- Modal global de confirmação para exclusões -->
<div class="ui-modal" id="deleteConfirmModal" hidden aria-hidden="true">
    <div class="ui-modal-backdrop" data-modal-close></div>
    <section class="ui-modal-card delete-modal-card" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle" aria-describedby="deleteModalText">
        <div class="ui-modal-icon danger" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9.5 3.75h5l.75 1.9h4v2H4.75v-2h4l.75-1.9Z" fill="currentColor"/>
                <path d="M6.75 8.75h10.5l-.8 10.2a1.4 1.4 0 0 1-1.4 1.3h-6.1a1.4 1.4 0 0 1-1.4-1.3l-.8-10.2Z" fill="currentColor" opacity=".92"/>
                <path d="M9.5 10.5v6.1M12 10.5v6.1M14.5 10.5v6.1" stroke="#fff" stroke-width="1.7" stroke-linecap="round"/>
            </svg>
        </div>
        <h2 id="deleteModalTitle">Tem certeza?</h2>
        <p id="deleteModalText">Você realmente deseja excluir este item? Essa ação não pode ser desfeita.</p>
        <div class="ui-modal-actions">
            <button type="button" class="modal-btn modal-btn-cancel" data-modal-close>Cancelar</button>
            <button type="button" class="modal-btn modal-btn-danger" id="deleteModalConfirm">Confirmar</button>
        </div>
    </section>
</div>


<!-- Modal de confirmação para remover a foto atual -->
<div class="ui-modal" id="removePhotoConfirmModal" hidden aria-hidden="true">
    <div class="ui-modal-backdrop" data-modal-close></div>
    <section class="ui-modal-card delete-modal-card" role="dialog" aria-modal="true" aria-labelledby="removePhotoModalTitle" aria-describedby="removePhotoModalText">
        <div class="ui-modal-icon danger" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M6.7 7.5h10.6l-.7 10.5a1.6 1.6 0 0 1-1.6 1.5H8.99a1.6 1.6 0 0 1-1.59-1.5L6.7 7.5Z" fill="currentColor" opacity=".92"/>
                <path d="M9 10.2v6.2M12 10.2v6.2M15 10.2v6.2M4.8 6.1h14.4M9.2 6.1l.8-2h4l.8 2" stroke="#fff" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <h2 id="removePhotoModalTitle">Remover foto atual?</h2>
        <p id="removePhotoModalText">Tem certeza que deseja remover a foto atual do seu perfil? Essa ação não pode ser desfeita.</p>
        <div class="ui-modal-actions">
            <button type="button" class="modal-btn modal-btn-cancel" data-modal-close>Cancelar</button>
            <button type="button" class="modal-btn modal-btn-danger" id="removePhotoModalConfirm">Remover</button>
        </div>
    </section>
</div>

<!-- Modal de confirmação/visualização da nova foto de perfil -->
<div class="ui-modal" id="photoConfirmModal" hidden aria-hidden="true">
    <div class="ui-modal-backdrop" data-modal-close></div>
    <section class="ui-modal-card photo-modal-card" role="dialog" aria-modal="true" aria-labelledby="photoModalTitle" aria-describedby="photoModalText">
        <div class="photo-modal-preview" id="photoModalPreview">
            <span class="avatar-fallback photo-modal-fallback">?</span>
        </div>
        <span class="section-kicker">FOTO DE PERFIL</span>
        <h2 id="photoModalTitle">Usar esta foto?</h2>
        <p id="photoModalText">Veja a pré-visualização antes de confirmar a nova foto do seu perfil.</p>
        <div class="ui-modal-actions">
            <button type="button" class="modal-btn modal-btn-cancel" id="photoModalCancel">Cancelar</button>
            <button type="button" class="modal-btn modal-btn-primary" id="photoModalConfirm">Confirmar</button>
        </div>
    </section>
</div>

