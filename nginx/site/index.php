<?php
    require_once 'interpreter/Engine.php';
    require_once 'interpreter/PageSystemReader.php';
    require_once 'interpreter/PageSection.php';
    require_once 'interpreter/PageJson.php';
    require_once 'interpreter/ComponentHTMLInterpreter.php';
    
    use interpreter\engine;
    use interpreter\PageSystemReader;
    
    $interpreter = new Engine();
    $reader = new PageSystemReader();
    
    // If the section or the page are not set, redirect to the home page.
    if (!isset($_GET['section']) || !isset($_GET['page'])) {
        header('Location: index.php?section=home&page=0');
    }
    
    // If the page exceeds the number of pages in the section, redirect them to the last page.
    $section = $reader->getSectionByNamespace($_GET['section']);
    $pages = $reader->getPagesForSection($section);
    
    if ($_GET['page'] > count($pages)) {
        header('Location: index.php?section=' . $_GET['section'] . '&page=' . count($pages));
    }
    
    // If the page is less than 0, redirect them to the first page.
    if ($_GET['page'] < 0) {
        header('Location: index.php?section=' . $_GET['section'] . '&page=0');
    }
        
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tutorial - Cliente/Servidor</title>
    <link rel="icon" href="./favicon.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="css/base.css">
    <link rel="stylesheet" type="text/css" href="css/components.css">
</head>
<body>

    <!-- The website header, common to all pages -->
    <div id="header">

        <div class="division-container">
            <a href="index.php?section=home&page=0">
                <img src="./assets/angel_devil.png" alt="logo" id="logo">
            </a>
            <div class="vertical-divider header-divider"></div>
        </div>
        
        <div class="header-middle">
            <div class="navigation">
                <?php echo $interpreter->buildNavigationElements(); ?>
            </div>
        </div>
        
        <div class="division-container">
            <div class="vertical-divider header-divider"></div>

            <div class="column-info-container right-side">
            <p>Alexandre Silva Nº3 12ºPGPS2</p>
            <p>Redes de Comunicação, 2023/2024</p>
            <p>Tutorial - Setup do Client/Server com Active Directory</p>
            </div>
        </div>

    </div>

    <!-- The content of the website, changes, being loaded dynamically with each page. -->
    <div id="content">
        <?php echo $interpreter->buildPage($_GET['section'], $_GET['page']); ?>
    </div>

    <div id="page-navigation">
        <?php echo $interpreter->buildSectionNavigation($_GET['section'], $_GET['page']); ?>
    </div>

    <div id="footer">
        
        <img src="./assets/aefc.png" alt="aefc-logo">

        <div class="column-info-container">
            <p>Agrupamento de Escolas do Forte da Casa (AEFC)</p>
            <p>Escola Secundária do Forte da Casa</p>
            <p>Curso Profissional de Gestão e Programação de Sistemas de Informação (PGPS)</p>
        </div>
    </div>



</body>
</html>