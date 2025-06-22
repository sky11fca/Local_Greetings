    <footer>
        <div class="container">
            <p>A project by Hasan MD Mehedi and Bazon Bogdan</p>
        </div>
    </footer>

    <?php if (isset($additionalScripts)): ?>
        <?php foreach ($additionalScripts as $script): ?>
            <script src="<?php echo TemplateHelper::escape($script); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <script src="<?php echo TemplateHelper::asset('js/main.js'); ?>"></script>
    <?php if (isset($currentPage) && $currentPage === 'admin'): ?>
        <script src="/local_greeter/public/js/admin.js"></script>
    <?php endif; ?>
    
    <?php if (isset($inlineScripts)): ?>
        <script><?php echo $inlineScripts; ?></script>
    <?php endif; ?>
</body>
</html> 