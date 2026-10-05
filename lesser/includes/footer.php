<footer class="site-footer">
    <div class="container footer-grid">
        <div><strong>YSA • Your Secrect Archive</strong><p>Projeto educacional em PHP, MySQL, HTML, CSS e JavaScript.</p></div>
        <div><span>Catálogo</span><a href="<?= e(url('index.php#animes')) ?>">Todos os animes</a><a href="<?= e(url('index.php#destaques')) ?>">Destaques</a></div>
        <div><span>Conta</span><?php if(current_user()): ?><a href="<?= e(url('pages/perfil.php')) ?>">Meu perfil</a><a href="<?= e(url('logout.php')) ?>">Sair</a><?php else: ?><a href="<?= e(url('pages/login.php')) ?>">Entrar</a><a href="<?= e(url('pages/register.php')) ?>">Criar conta</a><?php endif; ?></div>
    </div>
    <div class="container footer-bottom">Conteúdo de demonstração. Use apenas arquivos que você tenha autorização para disponibilizar.</div>
</footer>

<?php require __DIR__ . '/modals.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/hls.js@1.5.17/dist/hls.min.js"></script>
<script src="<?= e(url('assets/js/app.js')) ?>?v=20260930-player3"></script>
</body>
</html>

