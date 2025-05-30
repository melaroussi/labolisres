<?php
global $patientLogged,$conf;
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center text-white" href="<?php echo $conf["baseURL"]; ?>index.php">
            <img src="images/logo.png" alt="Logo" height="40" class="d-inline-block align-text-top me-2">
            <span class="fw-bold text-white"><?php echo getSrOption("laboNom"); ?></span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <?php if (isset($patientLogged) && $patientLogged->isAuth()): ?>
                <ul class="navbar-nav me-auto">
                    <?php if(!$_SESSION["accesPermalink"] || $_SESSION["accesPermalinkLevel"] == 1): ?>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center text-white" href='<?php echo $conf["baseURL"]; ?>consultation.php'>
                                <i class="fas fa-list me-2"></i> <?php echo _s("Liste des demandes"); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <div class="navbar-nav ms-auto">
                    <?php
                    $dataInter = Array(
                        'nom' => $patientLogged->nom, 
                        'prenom' => (isset($patientLogged->prenom)?$patientLogged->prenom:''),
                    ); 
                    $changePasswAutorise = false;
                    if ($patientLogged->niveau=="patient") {    
                        $typeInter = "Patient";
                        if(getSrOption("passwordPerso") > 0) {
                            $changePasswAutorise = true;
                        }
                    }
                    elseif($patientLogged->niveau=="medecin") {
                        $typeInter = "Médecin";
                        $changePasswAutorise = true;
                    }
                    else if($patientLogged->niveau=="correspondant") {
                        $typeInter = "Correspondant";
                        $changePasswAutorise = true;
                    }
                    else if($patientLogged->niveau=="preleveur") {
                        $typeInter = "Préleveur";
                        $changePasswAutorise = true;
                    }
                    ?>
                    
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-white text-primary me-2">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="d-none d-lg-block">
                                    <div class="fw-bold text-white"><?php echo $dataInter["prenom"]." ".$dataInter["nom"]; ?></div>
                                    <small class="text-white-50"><?php echo $typeInter; ?></small>
                                </div>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userDropdown">
                            <li class="dropdown-header d-lg-none">
                                <div class="fw-bold text-dark"><?php echo $dataInter["prenom"]." ".$dataInter["nom"]; ?></div>
                                <small class="text-muted"><?php echo $typeInter; ?></small>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <?php if($changePasswAutorise): ?>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href='<?php echo $conf["baseURL"]; ?>changePassword.php'>
                                        <i class="fas fa-key me-2 text-primary"></i> <?php echo _s("Changer le mot de passe"); ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center text-danger" href='<?php echo $conf["baseURL"]; ?>index.php?logout=1'>
                                    <i class="fas fa-sign-out-alt me-2"></i> <?php echo _s("Déconnexion"); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center text-white" href='<?php echo $conf["baseURL"]; ?>index.php'>
                            <i class="fas fa-sign-in-alt me-2"></i> <?php echo _s("Page d'identification"); ?>
                        </a>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>

<style>
.navbar {
    padding: 0.5rem 1rem;
    background-color: #0d6efd !important;
}

.navbar-brand {
    font-size: 1.25rem;
}

.navbar-dark .navbar-nav .nav-link {
    color: rgba(255, 255, 255, 1) !important;
}

.navbar-dark .navbar-nav .nav-link:hover {
    color: rgba(255, 255, 255, 0.8) !important;
}

.avatar-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.dropdown-menu {
    border: none;
    border-radius: 0.5rem;
    min-width: 200px;
}

.dropdown-item {
    padding: 0.5rem 1rem;
    color: #212529;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
}

.dropdown-header {
    padding: 0.5rem 1rem;
    background-color: #f8f9fa;
}

.nav-link {
    padding: 0.5rem 1rem;
    transition: all 0.2s ease-in-out;
    color: #fff !important;
}

.nav-link:hover {
    opacity: 0.8;
}

.navbar-toggler {
    border-color: rgba(255, 255, 255, 0.5);
}

.navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.75%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

@media (max-width: 991.98px) {
    .navbar-collapse {
        padding: 1rem 0;
        background-color: #0d6efd;
    }
    
    .navbar-nav {
        margin-top: 0.5rem;
    }
    
    .nav-item {
        margin: 0.25rem 0;
    }
    
    .dropdown-menu {
        background-color: #fff;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add active class to current nav item
    const currentLocation = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentLocation) {
            link.classList.add('active');
        }
    });
});
</script>
