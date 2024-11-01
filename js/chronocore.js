document.addEventListener('DOMContentLoaded', function() {
	
	// Selecteurs pour la configuration des inputs
	const postsIntervalInput = document.querySelector('#chron0striggeuss_posts_interval');
	const pagesIntervalInput = document.querySelector('#chron0striggeuss_pages_interval');
	const postsBackdateInput = document.querySelector('#chron0striggeuss_posts_backdate');
	const pagesBackdateInput = document.querySelector('#chron0striggeuss_pages_backdate');
	const activeSelector = document.querySelector('#chron0striggeuss_active');

	// Mise a jour des champs de backdate en fonction des intervalles
	function updateBackdateDefault(inputElement, backdateElement, defaultDays) {
		const interval = parseInt(inputElement.value, 10) || defaultDays;
		backdateElement.value = Math.max(interval - 1, 1);  // Assure la valeur minimum à 1
	}

	// Affichage conditionnel des lignes en fonction de l'activation
	function toggleFields() {
		const isActive = activeSelector.value;
		document.querySelectorAll('.posts-config').forEach(div => {
			const tr = div.closest('tr');
			tr.style.display = (isActive === '2' || isActive === '3') ? '' : 'none';
		});
		document.querySelectorAll('.pages-config').forEach(div => {
			const tr = div.closest('tr');
			tr.style.display = (isActive === '1' || isActive === '3') ? '' : 'none';
		});
	}

	
	// Associer les événement uniquement si les éléments existent

	if (postsIntervalInput) {
		postsIntervalInput.addEventListener('change', () => updateBackdateDefault(postsIntervalInput, postsBackdateInput, 30));
	}
	if (pagesIntervalInput) {
		pagesIntervalInput.addEventListener('change', () => updateBackdateDefault(pagesIntervalInput, pagesBackdateInput, 30));
	}
	if (activeSelector) {
		activeSelector.addEventListener('change', toggleFields);
	}

	// Initialisation des affichages
	
	if (postsIntervalInput) {
		updateBackdateDefault(postsIntervalInput, postsBackdateInput, 30);
	}
	if (pagesIntervalInput) {
		updateBackdateDefault(pagesIntervalInput, pagesBackdateInput, 30);
	}
	if (activeSelector) {
		toggleFields();
	}
});

// Gestion de la soumission du formulaire de randomisation
jQuery(document).ready(function($) {
	$('.randomize-posts-form').on('submit', function(e) {
		e.preventDefault();
		const nonce = $('#randomize_posts_nonce').val();
		$.post(ajaxurl, {
			action: 'randomize_posts',
			nonce: nonce
		}, function(response) {
			let messageContainer = $('#message-container');
			if (response.success) {
				messageContainer.css({ 'background': '#28a745' }).text(response.data).show();
			} else {
				messageContainer.css({ 'background': '#dc3545' }).text('Echec').show();
			}
			 // Programme la disparition du message après 5 secondes (5000 millisecondes)
			setTimeout(function() {
				messageContainer.fadeOut();  // Fonction fadeOut pour un effet de disparition progressif
			}, 3000);
		});
	});
});