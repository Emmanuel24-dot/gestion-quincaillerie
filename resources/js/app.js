const sidebar = document.querySelector('#adminSidebar');
const sidebarBackdrop = document.querySelector('[data-sidebar-backdrop]');
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
const mobileViewport = window.matchMedia('(max-width: 991.98px)');
const compactPreferenceKey = 'quincapro-sidebar-compact';

const positionCompactSubmenus = () => {
	const isCompact = document.body.classList.contains('sidebar-compact') && !mobileViewport.matches;
	const compactWidth = Number.parseFloat(getComputedStyle(document.body).getPropertyValue('--sidebar-compact-width')) || 80;
	const sidebarLeft = sidebar?.getBoundingClientRect().left ?? 0;
	const sidebarRight = sidebarLeft + compactWidth;

	document.querySelectorAll('[data-sidebar-group]').forEach((group) => {
		const submenu = group.querySelector('[data-sidebar-submenu]');
		const sectionButton = group.querySelector('[data-sidebar-section]');

		if (!isCompact || submenu?.hidden) {
			submenu?.style.removeProperty('--sidebar-flyout-top');
			submenu?.style.removeProperty('--sidebar-flyout-left');
			return;
		}

		const buttonTop = sectionButton.getBoundingClientRect().top;
		const panelHeight = Math.min(submenu.scrollHeight, window.innerHeight - 24);
		const top = Math.max(12, Math.min(buttonTop, window.innerHeight - panelHeight - 12));

		submenu.style.setProperty('--sidebar-flyout-top', `${top}px`);
		submenu.style.setProperty('--sidebar-flyout-left', `${sidebarRight + 8}px`);
	});
};

const closeMobileSidebar = () => {
	sidebar?.classList.remove('is-open');
	sidebarBackdrop?.classList.remove('is-visible');
	sidebarToggle?.setAttribute('aria-expanded', 'false');
	sidebarToggle?.setAttribute('aria-label', 'Ouvrir le menu');
};

const updateSidebarForViewport = () => {
	if (mobileViewport.matches) {
		document.body.classList.remove('sidebar-compact');
		sidebar?.classList.remove('is-open');
		sidebarBackdrop?.classList.remove('is-visible');
		sidebarToggle?.setAttribute('aria-expanded', 'false');
		sidebarToggle?.setAttribute('aria-label', 'Ouvrir le menu');
		return;
	}

	sidebar?.classList.remove('is-open');
	sidebarBackdrop?.classList.remove('is-visible');
	const isCompact = localStorage.getItem(compactPreferenceKey) === 'true';
	document.body.classList.toggle('sidebar-compact', isCompact);
	sidebarToggle?.setAttribute('aria-expanded', isCompact ? 'false' : 'true');
	sidebarToggle?.setAttribute('aria-label', isCompact ? 'Développer le menu' : 'Réduire le menu');
	positionCompactSubmenus();
};

updateSidebarForViewport();
mobileViewport.addEventListener('change', updateSidebarForViewport);

sidebarToggle?.addEventListener('click', () => {
	if (mobileViewport.matches) {
		const isOpen = sidebar?.classList.toggle('is-open') ?? false;
		sidebarBackdrop?.classList.toggle('is-visible', isOpen);
		sidebarToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		sidebarToggle.setAttribute('aria-label', isOpen ? 'Fermer le menu' : 'Ouvrir le menu');
		return;
	}

	const isCompact = document.body.classList.toggle('sidebar-compact');
	localStorage.setItem(compactPreferenceKey, isCompact ? 'true' : 'false');
	sidebarToggle.setAttribute('aria-expanded', isCompact ? 'false' : 'true');
	sidebarToggle.setAttribute('aria-label', isCompact ? 'Développer le menu' : 'Réduire le menu');
	positionCompactSubmenus();
});

document.querySelector('[data-sidebar-close]')?.addEventListener('click', closeMobileSidebar);
sidebarBackdrop?.addEventListener('click', closeMobileSidebar);

document.querySelectorAll('[data-sidebar-group]').forEach((group) => {
	const sectionButton = group.querySelector('[data-sidebar-section]');
	const submenu = group.querySelector('[data-sidebar-submenu]');

	sectionButton?.addEventListener('click', () => {
		const isExpanded = sectionButton.getAttribute('aria-expanded') === 'true';
		const willExpand = !isExpanded;

		sectionButton.setAttribute('aria-expanded', willExpand ? 'true' : 'false');
		sectionButton.classList.toggle('is-expanded', willExpand);
		group.classList.toggle('is-open', willExpand);
		if (submenu) {
			submenu.hidden = !willExpand;
		}
		positionCompactSubmenus();
	});
});

sidebar?.querySelector('.sidebar-nav')?.addEventListener('scroll', positionCompactSubmenus, { passive: true });
window.addEventListener('resize', positionCompactSubmenus);

document.addEventListener('click', (event) => {
	if (document.body.classList.contains('sidebar-compact') && !event.target.closest('[data-sidebar-group]')) {
		document.querySelectorAll('[data-sidebar-group].is-open').forEach((group) => {
			const sectionButton = group.querySelector('[data-sidebar-section]');
			const submenu = group.querySelector('[data-sidebar-submenu]');

			group.classList.remove('is-open');
			sectionButton?.classList.remove('is-expanded');
			sectionButton?.setAttribute('aria-expanded', 'false');
			if (submenu) {
				submenu.hidden = true;
			}
		});
	}

	const userMenu = document.querySelector('[data-user-menu]');
	const userDropdown = document.querySelector('[data-user-dropdown]');
	if (!userMenu?.contains(event.target) && !userDropdown?.contains(event.target)) {
		userDropdown?.classList.remove('is-open');
		userMenu?.setAttribute('aria-expanded', 'false');
	}
});

document.querySelector('[data-user-menu]')?.addEventListener('click', () => {
	const userMenu = document.querySelector('[data-user-menu]');
	const userDropdown = document.querySelector('[data-user-dropdown]');
	const isOpen = userDropdown?.classList.toggle('is-open') ?? false;

	userMenu?.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
});

document.addEventListener('keydown', (event) => {
	if (event.key === 'Escape') {
		closeMobileSidebar();
		document.querySelectorAll('[data-sidebar-group].is-open').forEach((group) => {
			if (document.body.classList.contains('sidebar-compact')) {
				group.classList.remove('is-open');
				const sectionButton = group.querySelector('[data-sidebar-section]');
				sectionButton?.classList.remove('is-expanded');
				sectionButton?.setAttribute('aria-expanded', 'false');
				const submenu = group.querySelector('[data-sidebar-submenu]');
				if (submenu) {
					submenu.hidden = true;
				}
			}
		});
		document.querySelector('[data-user-dropdown]')?.classList.remove('is-open');
		document.querySelector('[data-user-menu]')?.setAttribute('aria-expanded', 'false');
	}
});

const posForm = document.querySelector('[data-pos-form]');

if (posForm) {
	const searchInput = posForm.querySelector('[data-pos-search]');
	const productButtons = Array.from(posForm.querySelectorAll('[data-pos-product]'));
	const categoryButtons = posForm.querySelectorAll('[data-pos-category]');
	const noResult = posForm.querySelector('[data-pos-no-result]');
	const linesContainer = posForm.querySelector('[data-pos-lines]');
	const emptyCart = posForm.querySelector('[data-pos-empty]');
	const hiddenItems = posForm.querySelector('[data-pos-hidden-items]');
	const cartCount = posForm.querySelector('[data-pos-count]');
	const clearButton = posForm.querySelector('[data-pos-clear]');
	const customerSelect = posForm.querySelector('[data-pos-customer]');
	const walkInName = posForm.querySelector('[data-pos-walk-in-name]');
	const receivedInput = posForm.querySelector('[data-pos-received]');
	const quickAmounts = posForm.querySelector('[data-pos-quick-amounts]');
	const submitButton = posForm.querySelector('[data-pos-submit]');
	const partialHint = posForm.querySelector('[data-pos-partial-hint]');
	const changeBox = posForm.querySelector('[data-pos-change-box]');
	const cartPanel = posForm.querySelector('.pos-cart');
	const mobileBar = posForm.querySelector('[data-pos-mobile-bar]');
	const desktopViewport = window.matchMedia('(min-width: 992px)');
	let lastChangedProductId = null;

	const moneyFormatter = new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 });
	const quantityFormatter = new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 3 });
	const formatMoney = (amount) => `${moneyFormatter.format(amount)} FCFA`;
	const roundMoney = (amount) => Math.round(amount * 100) / 100;
	const roundQuantity = (quantity) => Math.round(quantity * 1000) / 1000;

	const products = new Map(productButtons.map((button) => [button.dataset.id, {
		name: button.dataset.name,
		price: Number.parseFloat(button.dataset.price) || 0,
		stock: Number.parseFloat(button.dataset.stock) || 0,
		unit: button.dataset.unit,
		button,
	}]));

	/** @type {Map<string, number>} */
	const cart = new Map();
	let activeCategory = '';
	let receivedWasEdited = receivedInput.hasAttribute('data-pos-edited');

	const cartTotal = () => roundMoney(Array.from(cart.entries())
		.reduce((total, [productId, quantity]) => total + roundMoney(quantity * products.get(productId).price), 0));

	const createElement = (tag, className, text) => {
		const element = document.createElement(tag);
		if (className) {
			element.className = className;
		}
		if (text !== undefined) {
			element.textContent = text;
		}
		return element;
	};

	const createButton = (className, label, ariaLabel, dataset) => {
		const button = createElement('button', className, label);
		button.type = 'button';
		button.setAttribute('aria-label', ariaLabel);
		Object.assign(button.dataset, dataset);
		return button;
	};

	const renderLine = (productId, quantity) => {
		const product = products.get(productId);
		const line = createElement('div', 'pos-line');
		line.dataset.productId = productId;

		const info = createElement('div', 'pos-line-info');
		info.append(
			createElement('strong', '', product.name),
			createElement('small', '', `${formatMoney(product.price)} / ${product.unit}`),
		);

		const remove = createButton('pos-line-remove', '', `Retirer ${product.name} du panier`, { posRemove: '' });
		remove.innerHTML = '<i class="bi bi-x-lg" aria-hidden="true"></i>';

		const minus = createButton('', '−', `Retirer une unité de ${product.name}`, { posStep: '-1' });
		const plus = createButton('', '+', `Ajouter une unité de ${product.name}`, { posStep: '1' });
		plus.disabled = quantity + 1 > product.stock;
		const input = createElement('input', 'form-control form-control-sm');
		Object.assign(input, { type: 'number', min: '0.001', step: '0.001', max: String(product.stock), value: String(quantity) });
		input.dataset.posQuantity = '';
		input.setAttribute('aria-label', `Quantité de ${product.name}`);
		const stepper = createElement('div', 'pos-stepper');
		stepper.append(minus, input, plus);

		const bottom = createElement('div', 'pos-line-bottom');
		bottom.append(stepper, createElement('strong', 'pos-line-total', formatMoney(roundMoney(quantity * product.price))));
		line.append(info, remove, bottom);

		if (quantity > product.stock) {
			input.classList.add('is-invalid');
			line.append(createElement('small', 'pos-line-error', `Stock disponible : ${quantityFormatter.format(product.stock)} ${product.unit}`));
		}

		return line;
	};

	const renderQuickAmounts = (total) => {
		quickAmounts.replaceChildren();
		if (total <= 0) {
			return;
		}
		const suggestions = new Set([500, 1000, 5000, 10000].map((step) => Math.ceil(total / step) * step).filter((amount) => amount > total));
		Array.from(suggestions).slice(0, 3).forEach((amount) => {
			quickAmounts.append(createButton('pos-quick-amount', moneyFormatter.format(amount), `Montant reçu : ${formatMoney(amount)}`, { posAmount: String(amount) }));
		});
	};

	const updatePayment = () => {
		const total = cartTotal();
		if (!receivedWasEdited) {
			receivedInput.value = String(total);
		}

		posForm.querySelector('[data-pos-total]').textContent = formatMoney(total);
		const received = Number.parseFloat(receivedInput.value) || 0;
		const difference = roundMoney(received - total);
		const isPartial = cart.size > 0 && difference < 0;
		const needsCustomer = isPartial && customerSelect.value === '';
		posForm.querySelector('[data-pos-change-label]').textContent = isPartial ? 'Reste à payer' : 'Monnaie à rendre';
		posForm.querySelector('[data-pos-change]').textContent = formatMoney(Math.abs(difference));
		changeBox.classList.toggle('is-due', isPartial);
		partialHint.hidden = !needsCustomer;

		const hasInvalidQuantity = Array.from(cart.entries()).some(([productId, quantity]) => quantity > products.get(productId).stock);
		submitButton.disabled = cart.size === 0 || hasInvalidQuantity || needsCustomer;
		renderQuickAmounts(total);
	};

	const renderCart = () => {
		const entries = Array.from(cart.entries());
		linesContainer.replaceChildren(...entries.map(([productId, quantity]) => renderLine(productId, quantity)));

		hiddenItems.replaceChildren();
		entries.forEach(([productId, quantity], index) => {
			[['product_id', productId], ['quantite', String(quantity)]].forEach(([field, value]) => {
				const input = document.createElement('input');
				Object.assign(input, { type: 'hidden', name: `items[${index}][${field}]`, value });
				hiddenItems.append(input);
			});
		});

		products.forEach((product, productId) => {
			const badge = product.button.querySelector('[data-pos-in-cart]');
			const quantity = cart.get(productId);
			product.button.classList.toggle('is-in-cart', quantity !== undefined);
			badge.hidden = quantity === undefined;
			badge.textContent = quantity === undefined ? '' : `× ${quantityFormatter.format(quantity)}`;
		});

		emptyCart.hidden = cart.size > 0;
		clearButton.hidden = cart.size === 0;
		cartCount.textContent = String(cart.size);
		updatePayment();

		mobileBar.hidden = cart.size === 0;
		mobileBar.querySelector('[data-pos-mobile-count]').textContent = String(cart.size);
		mobileBar.querySelector('[data-pos-mobile-total]').textContent = formatMoney(cartTotal());

		const changedLine = lastChangedProductId === null
			? null
			: linesContainer.querySelector(`.pos-line[data-product-id="${lastChangedProductId}"]`);
		lastChangedProductId = null;
		if (changedLine) {
			changedLine.classList.add('is-highlighted');
			if (desktopViewport.matches) {
				linesContainer.scrollTop = changedLine.offsetTop - linesContainer.offsetTop - 8;
			} else {
				mobileBar.classList.remove('is-bumped');
				void mobileBar.offsetWidth;
				mobileBar.classList.add('is-bumped');
			}
		}
	};

	const setQuantity = (productId, quantity) => {
		lastChangedProductId = productId;
		const roundedQuantity = roundQuantity(quantity);
		if (roundedQuantity <= 0) {
			cart.delete(productId);
		} else {
			cart.set(productId, roundedQuantity);
		}
		renderCart();
	};

	const flashRefused = (product) => {
		product.button.classList.add('is-refused');
		setTimeout(() => product.button.classList.remove('is-refused'), 400);
	};

	const addProduct = (productId) => {
		const product = products.get(productId);
		const currentQuantity = cart.get(productId) ?? 0;
		if (currentQuantity + 1 <= product.stock) {
			setQuantity(productId, currentQuantity + 1);
		} else if (currentQuantity < product.stock) {
			setQuantity(productId, product.stock);
		} else {
			flashRefused(product);
		}
	};

	const visibleProducts = () => productButtons.filter((button) => !button.hidden);

	const applyFilters = () => {
		const terms = searchInput.value.trim().toLowerCase().split(/\s+/).filter(Boolean);
		productButtons.forEach((button) => {
			const matchesCategory = activeCategory === '' || button.dataset.category === activeCategory;
			const matchesSearch = terms.every((term) => button.dataset.search.includes(term));
			button.hidden = !(matchesCategory && matchesSearch);
		});
		noResult.hidden = visibleProducts().length > 0;
	};

	productButtons.forEach((button) => button.addEventListener('click', () => addProduct(button.dataset.id)));

	searchInput.addEventListener('input', applyFilters);
	searchInput.addEventListener('keydown', (event) => {
		if (event.key !== 'Enter') {
			return;
		}
		event.preventDefault();
		const [firstProduct] = visibleProducts();
		if (firstProduct && searchInput.value.trim() !== '') {
			addProduct(firstProduct.dataset.id);
			searchInput.value = '';
			applyFilters();
		}
	});

	categoryButtons.forEach((button) => button.addEventListener('click', () => {
		activeCategory = button.dataset.posCategory;
		categoryButtons.forEach((categoryButton) => {
			const isActive = categoryButton === button;
			categoryButton.classList.toggle('is-active', isActive);
			categoryButton.setAttribute('aria-pressed', isActive ? 'true' : 'false');
		});
		applyFilters();
	}));

	linesContainer.addEventListener('click', (event) => {
		const line = event.target.closest('.pos-line');
		if (!line) {
			return;
		}
		const productId = line.dataset.productId;
		if (event.target.closest('[data-pos-remove]')) {
			setQuantity(productId, 0);
			return;
		}
		const stepButton = event.target.closest('[data-pos-step]');
		if (stepButton) {
			const nextQuantity = (cart.get(productId) ?? 0) + Number(stepButton.dataset.posStep);
			setQuantity(productId, Math.min(nextQuantity, products.get(productId).stock));
		}
	});

	linesContainer.addEventListener('change', (event) => {
		if (event.target.matches('[data-pos-quantity]')) {
			setQuantity(event.target.closest('.pos-line').dataset.productId, Number.parseFloat(event.target.value) || 0);
		}
	});

	mobileBar.addEventListener('click', () => {
		cartPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
	});

	clearButton.addEventListener('click', () => {
		cart.clear();
		renderCart();
		searchInput.focus();
	});

	receivedInput.addEventListener('input', () => {
		receivedWasEdited = true;
		updatePayment();
	});

	posForm.querySelector('[data-pos-exact]').addEventListener('click', () => {
		receivedWasEdited = false;
		updatePayment();
	});

	quickAmounts.addEventListener('click', (event) => {
		const button = event.target.closest('[data-pos-amount]');
		if (button) {
			receivedWasEdited = true;
			receivedInput.value = button.dataset.posAmount;
			updatePayment();
		}
	});

	customerSelect.addEventListener('change', () => {
		walkInName.hidden = customerSelect.value !== '';
		updatePayment();
	});

	posForm.addEventListener('submit', (event) => {
		if (submitButton.disabled) {
			event.preventDefault();
			return;
		}
		submitButton.disabled = true;
		submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Enregistrement…';
	});

	try {
		JSON.parse(posForm.dataset.posInitialItems || '[]').forEach((item) => {
			const productId = String(item.product_id ?? '');
			const quantity = Number.parseFloat(item.quantite);
			if (products.has(productId) && quantity > 0) {
				cart.set(productId, roundQuantity(quantity));
			}
		});
	} catch {
		cart.clear();
	}

	walkInName.hidden = customerSelect.value !== '';
	renderCart();
	mobileBar.classList.remove('is-bumped');
	linesContainer.querySelectorAll('.is-highlighted').forEach((line) => line.classList.remove('is-highlighted'));
}

const purchaseForm = document.querySelector('[data-purchase-form]');

if (purchaseForm) {
	const purchaseItems = purchaseForm.querySelector('[data-purchase-items]');
	const purchaseItemTemplate = purchaseForm.querySelector('[data-purchase-item-template]');
	const existingPurchaseItemIndexes = Array.from(purchaseItems.querySelectorAll('[data-purchase-product]'))
		.map((productSelect) => Number.parseInt(productSelect.name.match(/^items\[(\d+)\]/)?.[1] ?? '-1', 10));
	let nextPurchaseItemIndex = Math.max(-1, ...existingPurchaseItemIndexes) + 1;

	const formatPurchaseMoney = (amount) => `${new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(amount)} FCFA`;

	const updatePurchaseTotals = () => {
		let purchaseTotal = 0;

		purchaseItems.querySelectorAll('[data-purchase-item]').forEach((row) => {
			const quantity = Number.parseFloat(row.querySelector('[data-purchase-quantity]').value) || 0;
			const unitPrice = Number.parseFloat(row.querySelector('[data-purchase-price]').value) || 0;
			const lineTotal = Math.round(quantity * unitPrice * 100) / 100;
			purchaseTotal += lineTotal;
			row.querySelector('[data-purchase-line-total]').textContent = formatPurchaseMoney(lineTotal);
		});

		purchaseForm.querySelector('[data-purchase-total]').textContent = formatPurchaseMoney(Math.round(purchaseTotal * 100) / 100);
	};

	const updatePurchaseRemoveButtons = () => {
		const rows = purchaseItems.querySelectorAll('[data-purchase-item]');
		rows.forEach((row) => {
			row.querySelector('[data-remove-purchase-item]').disabled = rows.length === 1;
		});
	};

	purchaseItems.querySelectorAll('[data-purchase-product]').forEach((productSelect) => {
		const priceInput = productSelect.closest('[data-purchase-item]').querySelector('[data-purchase-price]');
		if (productSelect.value && priceInput.value === '') {
			priceInput.value = productSelect.selectedOptions[0]?.dataset.price ?? '';
		}
	});

	purchaseItems.addEventListener('input', updatePurchaseTotals);
	purchaseItems.addEventListener('change', (event) => {
		if (event.target.matches('[data-purchase-product]')) {
			const priceInput = event.target.closest('[data-purchase-item]').querySelector('[data-purchase-price]');
			priceInput.value = event.target.selectedOptions[0]?.dataset.price ?? '';
		}
		updatePurchaseTotals();
	});

	purchaseForm.querySelector('[data-add-purchase-item]').addEventListener('click', () => {
		const rowMarkup = purchaseItemTemplate.innerHTML.replaceAll('__INDEX__', String(nextPurchaseItemIndex));
		purchaseItems.insertAdjacentHTML('beforeend', rowMarkup);
		nextPurchaseItemIndex += 1;
		updatePurchaseRemoveButtons();
		updatePurchaseTotals();
	});

	purchaseItems.addEventListener('click', (event) => {
		const removeButton = event.target.closest('[data-remove-purchase-item]');
		if (removeButton && purchaseItems.querySelectorAll('[data-purchase-item]').length > 1) {
			removeButton.closest('[data-purchase-item]').remove();
			updatePurchaseRemoveButtons();
			updatePurchaseTotals();
		}
	});

	updatePurchaseRemoveButtons();
	updatePurchaseTotals();
}

document.querySelectorAll('[data-sale-print]').forEach((button) => {
	button.addEventListener('click', () => window.print());
});

document.querySelectorAll('[data-invoice-print]').forEach((button) => {
	button.addEventListener('click', () => window.print());
});

const installButtons = document.querySelectorAll('[data-pwa-install]');
const manifestLink = document.querySelector('link[rel="manifest"]');
let deferredInstallPrompt = null;

const isInstalledAsApp = window.matchMedia('(display-mode: standalone)').matches
	|| window.navigator.standalone === true;

installButtons.forEach((button) => {
	button.hidden = isInstalledAsApp;
});

if ('serviceWorker' in navigator && window.isSecureContext && manifestLink) {
	const serviceWorkerUrl = new URL('service-worker.js', manifestLink.href);
	const serviceWorkerScope = new URL('./', manifestLink.href).pathname;

	navigator.serviceWorker.register(serviceWorkerUrl, { scope: serviceWorkerScope })
		.catch((error) => console.error('Impossible d’enregistrer le service worker QuincaPro.', error));
}

window.addEventListener('beforeinstallprompt', (event) => {
	event.preventDefault();
	deferredInstallPrompt = event;
	installButtons.forEach((button) => {
		button.hidden = false;
	});
});

installButtons.forEach((button) => {
	button.addEventListener('click', async () => {
		if (!deferredInstallPrompt) {
			return;
		}

		await deferredInstallPrompt.prompt();
		await deferredInstallPrompt.userChoice;
		deferredInstallPrompt = null;
		installButtons.forEach((installButton) => {
			installButton.hidden = true;
		});
	});
});

window.addEventListener('appinstalled', () => {
	deferredInstallPrompt = null;
	installButtons.forEach((button) => {
		button.hidden = true;
	});
});
