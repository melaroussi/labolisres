<?php
	global $conf, $patientLogged;
?>
<div class="sidebar bg-white shadow-sm" id="leftMenu">
    <div class="sidebar-header p-3 border-bottom">
        <h5 class="mb-0 text-primary fw-bold">
            <i class="fas fa-bars me-2"></i><?php echo _s("Menu"); ?>
        </h5>
    </div>
    
    <div class="sidebar-content p-3">
        <?php if (isset($patientLogged) && $patientLogged->isAuth()): ?>
            <ul class="nav flex-column">
                <?php if(!$_SESSION["accesPermalink"] || $_SESSION["accesPermalinkLevel"] == 1): ?>
                    <li class="nav-item mb-2">
                        <a class="nav-link d-flex align-items-center" href='<?php echo $conf["baseURL"]; ?>consultation.php'>
                            <i class="fas fa-list me-2 text-primary"></i>
                            <?php echo _s("Liste des demandes"); ?>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php if($patientLogged->niveau=="medecin"): ?>
                    <li class="nav-item mb-2">
                        <a class="nav-link d-flex align-items-center" href='<?php echo $conf["baseURL"]; ?>prescription.php'>
                            <i class="fas fa-prescription me-2 text-primary"></i>
                            <?php echo _s("Nouvelle prescription"); ?>
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link d-flex align-items-center" href='<?php echo $conf["baseURL"]; ?>listePrescription.php'>
                            <i class="fas fa-clipboard-list me-2 text-primary"></i>
                            <?php echo _s("Liste des prescriptions"); ?>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php if($patientLogged->niveau=="preleveur"): ?>
                    <li class="nav-item mb-2">
                        <a class="nav-link d-flex align-items-center" href='<?php echo $conf["baseURL"]; ?>prelevement.php'>
                            <i class="fas fa-vial me-2 text-primary"></i>
                            <?php echo _s("Prélèvements"); ?>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php if($patientLogged->niveau=="patient"): ?>
                    <li class="nav-item mb-2">
                        <a class="nav-link d-flex align-items-center" href='<?php echo $conf["baseURL"]; ?>resultats.php'>
                            <i class="fas fa-file-medical me-2 text-primary"></i>
                            <?php echo _s("Mes résultats"); ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<style>
/* Override any existing styles with higher specificity */
#leftMenu.sidebar {
    min-height: calc(100vh - 56px) !important;
    position: sticky !important;
    top: 56px !important;
    background-color: #ffffff !important;
    z-index: 1000 !important;
}

#leftMenu .sidebar-header {
    background-color: #f8f9fa !important;
    border-bottom: 1px solid #dee2e6 !important;
}

#leftMenu .sidebar-content {
    background-color: #ffffff !important;
}

#leftMenu .nav-link {
    padding: 0.75rem 1rem !important;
    border-radius: 0.25rem !important;
    transition: all 0.2s ease-in-out !important;
    color: #2c3e50 !important;
    font-weight: 500 !important;
    text-decoration: none !important;
}

#leftMenu .nav-link:hover {
    background-color: #e9ecef !important;
    color: #0d6efd !important;
    transform: translateX(5px) !important;
    text-decoration: none !important;
}

#leftMenu .nav-link.active {
    background-color: #e9ecef !important;
    color: #0d6efd !important;
    font-weight: 600 !important;
    border-left: 3px solid #0d6efd !important;
}

#leftMenu .nav-link i {
    width: 20px !important;
    text-align: center !important;
    font-size: 1.1em !important;
    color: #0d6efd !important;
}

#leftMenu .sidebar-header h5 {
    color: #0d6efd !important;
    font-size: 1.2rem !important;
    font-weight: 700 !important;
}

@media (max-width: 991.98px) {
    #leftMenu.sidebar {
        min-height: auto !important;
        position: relative !important;
        top: 0 !important;
        margin-bottom: 1rem !important;
    }
    
    #leftMenu .nav-link {
        padding: 0.5rem 1rem !important;
    }
    
    #leftMenu .nav-link:hover {
        transform: none !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add active class to current nav item
    const currentLocation = window.location.pathname;
    const navLinks = document.querySelectorAll('#leftMenu .nav-link');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentLocation) {
            link.classList.add('active');
        }
    });
});
</script>
