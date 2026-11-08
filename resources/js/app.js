import './bootstrap';

const orderStorageKey = 'fromdarna.orders.v1';
const shiftStorageKey = 'fromdarna.courier.available.v1';

function readOrders() {
	try {
		const orders = JSON.parse(localStorage.getItem(orderStorageKey) || '[]');
		return Array.isArray(orders) ? orders : [];
	} catch {
		return [];
	}
}

function writeOrders(orders) {
	localStorage.setItem(orderStorageKey, JSON.stringify(orders));
}

function updateOrder(orderId, changes) {
	const orders = readOrders();
	const order = orders.find((item) => item.id === orderId);
	if (!order) return;
	Object.assign(order, changes);
	writeOrders(orders);
}

function escapeHtml(value) {
	return String(value).replace(/[&<>"']/g, (character) => ({
		'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
	}[character]));
}

const portalRole = document.body.dataset.portalRole;

if (portalRole) {
	const orderStatus = {
		placed: ['À confirmer', 'state-new'],
		accepted: ['En préparation', 'state-preparing'],
		ready: ['Prête', 'state-ready'],
		assigned: ['Livreur attribué', 'state-preparing'],
		picked_up: ['En livraison', 'state-preparing'],
		delivered: ['Livrée', 'state-ready'],
		rejected: ['Refusée', 'state-late'],
	};
	const money = (amount) => `${Number(amount || 0).toFixed(3).replace('.', ',')} TND`;
	const receivedAt = (value) => new Date(value).toLocaleTimeString('fr-TN', { hour: '2-digit', minute: '2-digit' });

	function renderPartnerOrders() {
		const orders = readOrders();
		const body = document.querySelector('#partner-orders');
		if (!body) return;
		const partnerOrders = orders.filter((order) => order.status !== 'rejected');
		const actionable = partnerOrders.filter((order) => ['placed', 'accepted'].includes(order.status)).length;
		body.innerHTML = partnerOrders.map((order) => {
			const [label, className] = orderStatus[order.status] || orderStatus.placed;
			const itemCount = order.items.reduce((total, item) => total + item.quantity, 0);
			const action = order.status === 'placed'
				? `<button class="small-primary" data-order-action="accept" data-order-id="${escapeHtml(order.id)}" type="button">Accepter</button><button class="small-quiet" data-order-action="reject" data-order-id="${escapeHtml(order.id)}" type="button">Refuser</button>`
				: order.status === 'accepted'
					? `<button class="small-primary" data-order-action="ready" data-order-id="${escapeHtml(order.id)}" type="button">Marquer prête</button>`
					: '<span class="muted-action">Aucune action requise</span>';
			return `<tr><td><strong>#${escapeHtml(order.id)}</strong><small>${escapeHtml(order.customer)} · ${escapeHtml(order.phone)}</small></td><td>${receivedAt(order.createdAt)}</td><td>${itemCount} article${itemCount > 1 ? 's' : ''}</td><td>${money(order.total)}</td><td><span class="state-pill ${className}">${label}</span></td><td class="row-actions">${action}</td></tr>`;
		}).join('');
		document.querySelector('#partner-orders-empty').hidden = partnerOrders.length > 0;
		document.querySelector('#partner-orders-received').textContent = partnerOrders.length;
		document.querySelector('#partner-pending-count').textContent = actionable;
		document.querySelector('#partner-total-to-settle').innerHTML = `${money(partnerOrders.reduce((total, order) => total + order.total, 0)).replace(' TND', '')} <em>TND</em>`;
	}

	function renderOpsOrders() {
		const body = document.querySelector('#ops-orders');
		if (!body) return;
		const activeOrders = readOrders().filter((order) => !['delivered', 'rejected'].includes(order.status));
		const unassigned = activeOrders.filter((order) => ['placed', 'ready'].includes(order.status));
		const lateOrders = activeOrders.filter((order) => Date.now() - new Date(order.createdAt).getTime() > 40 * 60 * 1000);
		const currentFilter = document.querySelector('.filter-pill.is-selected')?.dataset.opsFilter || 'all';
		const visibleOrders = currentFilter === 'unassigned'
			? activeOrders.filter((order) => ['placed', 'ready'].includes(order.status))
			: currentFilter === 'late' ? lateOrders : activeOrders;
		body.innerHTML = visibleOrders.map((order) => {
			const [label, className] = orderStatus[order.status] || orderStatus.placed;
			const courierCell = order.courier
				? escapeHtml(order.courier)
				: `<select aria-label="Choisir un livreur" data-courier-select="${escapeHtml(order.id)}"><option value="">Sélectionner</option><option>Livreur Grombalia 1</option><option>Livreur Grombalia 2</option><option>Livreur Grombalia 3</option></select>`;
			const action = order.courier ? '<span class="muted-action">Attribuée</span>' : `<button class="small-primary" data-assign-order="${escapeHtml(order.id)}" type="button">Attribuer</button>`;
			return `<tr><td><strong>#${escapeHtml(order.id)}</strong><small>${money(order.total)} · cash</small></td><td>${escapeHtml(order.restaurant)}</td><td>${escapeHtml(order.address)}<small>${escapeHtml(order.customer)}</small></td><td>${receivedAt(order.createdAt)}</td><td><span class="state-pill ${className}">${label}</span></td><td>${courierCell}</td><td>${action}</td></tr>`;
		}).join('');
		document.querySelector('#ops-orders-empty').hidden = visibleOrders.length > 0;
		document.querySelector('#ops-total-count').textContent = activeOrders.length;
		document.querySelector('#ops-unassigned-count').textContent = unassigned.length;
		document.querySelector('#ops-late-count').textContent = lateOrders.length;
		document.querySelector('#ops-active-count').textContent = activeOrders.length;
		document.querySelector('#ops-active-unassigned').textContent = unassigned.length;
		const assignedOrders = activeOrders.filter((order) => order.courier);
		const roster = document.querySelector('#ops-courier-roster');
		if (roster) {
			const couriers = [...new Set(assignedOrders.map((order) => order.courier))];
			roster.innerHTML = couriers.length ? couriers.map((courier) => {
				const jobs = assignedOrders.filter((order) => order.courier === courier);
				return `<div><i class="roster-dot"></i><strong>${escapeHtml(courier)}</strong><span>${jobs.length} course${jobs.length > 1 ? 's' : ''}</span><small>${money(jobs.reduce((total, order) => total + order.total, 0))} cash</small></div>`;
			}).join('') : '<p class="portal-empty">Aucune course attribuée.</p>';
		}
		const cashHeld = readOrders().filter((order) => order.status === 'delivered' && !order.cashRemitted).reduce((total, order) => total + order.total, 0);
		const cashHeldElement = document.querySelector('#ops-cash-held');
		if (cashHeldElement) cashHeldElement.textContent = money(cashHeld);
	}

	function renderCourierOrders() {
		const container = document.querySelector('#courier-live-orders');
		if (!container) return;
		document.querySelector('.courier-layout')?.remove();
		const available = localStorage.getItem(shiftStorageKey) !== 'false';
		const assignedOrders = readOrders().filter((order) => order.courier && ['assigned', 'picked_up'].includes(order.status));
		container.innerHTML = assignedOrders.length ? assignedOrders.map((order) => {
			const onRoute = order.status === 'picked_up';
			return `<article class="portal-panel courier-job"><div class="panel-heading"><div><p class="eyebrow">COMMANDE #${escapeHtml(order.id)}</p><h2>${escapeHtml(order.restaurant)}</h2></div><span class="state-pill ${onRoute ? 'state-preparing' : 'state-new'}">${onRoute ? 'En livraison' : 'À récupérer'}</span></div><div class="route-stops"><div class="route-stop"><span class="route-marker pickup-marker">1</span><div><small>RETRAIT</small><strong>${escapeHtml(order.restaurant)}</strong><p>Commande pour ${escapeHtml(order.customer)}</p></div></div><span class="route-connector"></span><div class="route-stop"><span class="route-marker drop-marker">2</span><div><small>LIVRAISON</small><strong>${escapeHtml(order.address)}</strong><p>${escapeHtml(order.phone)}</p></div></div></div><div class="delivery-order"><span><small>À encaisser en espèces</small><strong>${money(order.total)}</strong></span><span><small>Votre course</small><strong>Confirmée</strong></span></div><button class="delivery-action" data-delivery-order="${escapeHtml(order.id)}" type="button">${onRoute ? 'Confirmer la livraison' : 'Confirmer le retrait'} <span>→</span></button></article>`;
		}).join('') : '<p class="portal-empty">Aucune course ne vous est attribuée pour le moment.</p>';
		const allOrders = readOrders();
		const delivered = allOrders.filter((order) => order.status === 'delivered');
		const history = document.querySelector('#courier-history');
		if (history) {
			history.innerHTML = delivered.length ? delivered.map((order) => `<div class="history-row"><strong>#${escapeHtml(order.id)}</strong><span>${escapeHtml(order.restaurant)} · ${receivedAt(order.createdAt)}</span><span>Livrée</span><strong>${money(order.total)}</strong></div>`).join('') : '<p class="portal-empty">Aucune course terminée.</p>';
		}
		document.querySelector('#courier-delivered-count').textContent = delivered.length;
		document.querySelector('#courier-earnings').innerHTML = `${money(delivered.length * 3).replace(' TND', '')} <em>TND</em>`;
		document.querySelector('#courier-cash-held').innerHTML = `${money(assignedOrders.reduce((total, order) => total + order.total, 0)).replace(' TND', '')} <em>TND</em>`;
		const shiftLabel = document.querySelector('[data-shift-label]');
		if (shiftLabel) {
			shiftLabel.textContent = available ? 'Vous êtes disponible' : 'Vous êtes indisponible';
			document.querySelector('[data-shift-indicator]').classList.toggle('is-offline', !available);
			document.querySelector('[data-toggle-shift]').textContent = available ? 'Passer indisponible' : 'Reprendre le service';
		}
	}

	function renderPortalOrders() {
		renderPartnerOrders();
		renderOpsOrders();
		renderCourierOrders();
	}

	document.body.addEventListener('click', (event) => {
		const actionButton = event.target.closest('[data-order-action]');
		if (actionButton) {
			const actions = { accept: { status: 'accepted' }, ready: { status: 'ready' }, reject: { status: 'rejected' } };
			updateOrder(actionButton.dataset.orderId, actions[actionButton.dataset.orderAction]);
			renderPortalOrders();
			return;
		}
		const assignButton = event.target.closest('[data-assign-order]');
		if (assignButton) {
			const select = document.querySelector(`[data-courier-select="${CSS.escape(assignButton.dataset.assignOrder)}"]`);
			if (!select?.value) {
				select?.focus();
				return;
			}
			updateOrder(assignButton.dataset.assignOrder, { courier: select.value, status: 'assigned' });
			renderPortalOrders();
			return;
		}
		const deliveryButton = event.target.closest('[data-delivery-order]');
		if (deliveryButton) {
			const order = readOrders().find((item) => item.id === deliveryButton.dataset.deliveryOrder);
			updateOrder(deliveryButton.dataset.deliveryOrder, { status: order.status === 'assigned' ? 'picked_up' : 'delivered' });
			renderPortalOrders();
		}
	});

	document.querySelector('[data-toggle-store]')?.addEventListener('click', (event) => {
		const button = event.currentTarget;
		const open = button.textContent.trim() === 'Mettre en pause';
		button.textContent = open ? 'Reprendre les commandes' : 'Mettre en pause';
		const state = document.querySelector('.open-state');
		state.innerHTML = `<i style="background:${open ? '#c59046' : '#41a878'}"></i>${open ? 'En pause' : 'Ouvert'}`;
	});

	document.querySelector('[data-toggle-shift]')?.addEventListener('click', (event) => {
		const button = event.currentTarget;
		const available = button.textContent.trim() === 'Passer indisponible';
		localStorage.setItem(shiftStorageKey, String(!available));
		renderCourierOrders();
	});

	document.querySelector('[data-courier-accept]')?.addEventListener('click', (event) => {
		event.currentTarget.textContent = 'Course acceptée';
		event.currentTarget.disabled = true;
	});

	document.querySelectorAll('.filter-pill').forEach((button) => {
		button.addEventListener('click', () => {
			document.querySelectorAll('.filter-pill').forEach((filter) => filter.classList.remove('is-selected'));
			button.classList.add('is-selected');
			renderOpsOrders();
		});
	});

	document.querySelector('[data-cash-action]')?.addEventListener('click', (event) => {
		const orders = readOrders();
		const pendingRemittance = orders.filter((order) => order.status === 'delivered' && !order.cashRemitted);
		if (!pendingRemittance.length) {
			event.currentTarget.textContent = 'Aucune remise en attente';
			return;
		}
		pendingRemittance.forEach((order) => { order.cashRemitted = true; });
		writeOrders(orders);
		event.currentTarget.textContent = 'Remise enregistrée';
		renderPortalOrders();
	});

	renderPortalOrders();
	window.addEventListener('storage', (event) => {
		if (event.key === orderStorageKey || event.key === shiftStorageKey) renderPortalOrders();
	});
}

const restaurants = {
	dar: {
		name: 'Dar El Kaskrout',
		items: [
			{ name: 'Kaskrout escalope', description: 'Escalope grillée, salade méchouia, harissa et frites.', price: 8.5 },
			{ name: 'Chapati maison', description: 'Œuf, fromage, thon et pommes de terre.', price: 7 },
			{ name: 'Kaskrout merguez', description: 'Merguez grillées, salade fraîche et sauce maison.', price: 9.5 },
		],
	},
	piazza: {
		name: 'La Piazza Grombalia',
		items: [
			{ name: 'Pizza Margherita', description: 'Tomate, mozzarella, basilic frais.', price: 16 },
			{ name: 'Pizza Tunisienne', description: 'Merguez, poivron, olives et œuf.', price: 21 },
			{ name: 'Penne arrabbiata', description: 'Sauce tomate relevée, parmesan.', price: 17 },
		],
	},
	mechoui: {
		name: 'Le Mechoui du Cap Bon',
		items: [
			{ name: 'Poulet grillé', description: 'Poulet rôti, salade du jour et frites.', price: 19 },
			{ name: 'Ojja merguez', description: 'Merguez, tomate, poivron et œufs.', price: 15 },
			{ name: 'Grillade mixte', description: 'Sélection de viandes grillées et accompagnement.', price: 26 },
		],
	},
	zina: {
		name: 'Maison Zina',
		items: [
			{ name: 'Tarte aux fruits', description: 'Crème légère, fruits de saison.', price: 6.5 },
			{ name: 'Mille-feuille', description: 'Feuilletage croustillant, crème pâtissière.', price: 5 },
			{ name: 'Café crème', description: 'Café fraîchement préparé.', price: 3.5 },
		],
	},
};

const cart = { restaurantId: null, items: [] };
const menuDialog = document.querySelector('#menu-dialog');
const cartDialog = document.querySelector('#cart-dialog');
const checkoutDialog = document.querySelector('#checkout-dialog');
const backdrop = document.querySelector('#dialog-backdrop');
const formatPrice = (price) => `${price.toFixed(3).replace('.', ',')} TND`;
function showDialog(dialog) {
	backdrop.classList.add('is-visible');
	dialog.showModal();
}

function closeDialogs() {
	menuDialog.close();
	cartDialog.close();
	checkoutDialog.close();
	backdrop.classList.remove('is-visible');
}

function openMenu(restaurantId) {
	const restaurant = restaurants[restaurantId];
	document.querySelector('#menu-title').textContent = restaurant.name;
	document.querySelector('#menu-items').innerHTML = restaurant.items.map((item, index) => `
		<article class="menu-item">
			<div><h3>${item.name}</h3><p>${item.description}</p></div>
			<div class="menu-item-side"><span>${formatPrice(item.price)}</span><button class="add-item" type="button" data-add="${index}" data-restaurant="${restaurantId}" aria-label="Ajouter ${item.name}">+</button></div>
		</article>
	`).join('');
	showDialog(menuDialog);
}

function renderCart() {
	const restaurant = restaurants[cart.restaurantId];
	const itemsElement = document.querySelector('#cart-items');
	const count = cart.items.reduce((total, item) => total + item.quantity, 0);
	const subtotal = cart.items.reduce((total, item) => total + item.price * item.quantity, 0);
	document.querySelector('#cart-count').textContent = count;
	document.querySelector('#mobile-cart-count').textContent = count;
	document.querySelector('#cart-restaurant').textContent = restaurant ? restaurant.name : '';
	document.querySelector('#cart-subtotal').textContent = formatPrice(subtotal);
	document.querySelector('#cart-total').textContent = formatPrice(subtotal + (count ? 3 : 0));
	document.querySelector('#checkout-button').disabled = count === 0;
	itemsElement.innerHTML = count ? cart.items.map((item) => `
		<div class="cart-line">
			<span>${item.name}</span>
			<div class="quantity-control"><button type="button" data-quantity="-1" data-item="${item.name}" aria-label="Retirer un ${item.name}">−</button><span>${item.quantity}</span><button type="button" data-quantity="1" data-item="${item.name}" aria-label="Ajouter un ${item.name}">+</button></div>
			<strong>${formatPrice(item.price * item.quantity)}</strong>
		</div>
	`).join('') : '<p class="cart-empty">Votre panier attend sa première bonne idée.</p>';
}

function addToCart(restaurantId, itemIndex) {
	if (cart.restaurantId && cart.restaurantId !== restaurantId) {
		cart.restaurantId = restaurantId;
		cart.items = [];
	}
	cart.restaurantId = restaurantId;
	const item = restaurants[restaurantId].items[itemIndex];
	const existing = cart.items.find((line) => line.name === item.name);
	if (existing) existing.quantity += 1;
	else cart.items.push({ ...item, quantity: 1 });
	renderCart();
}


if (menuDialog && cartDialog && backdrop) {
document.querySelectorAll('[data-menu]').forEach((button) => {
	button.addEventListener('click', () => openMenu(button.dataset.menu));
});

document.querySelector('#menu-items').addEventListener('click', (event) => {
	const button = event.target.closest('[data-add]');
	if (!button) return;
	addToCart(button.dataset.restaurant, Number(button.dataset.add));
	button.textContent = '✓';
	window.setTimeout(() => { if (button.isConnected) button.textContent = '+'; }, 700);
});

document.querySelectorAll('[data-close]').forEach((button) => {
	button.addEventListener('click', closeDialogs);
});
backdrop.addEventListener('click', closeDialogs);
document.querySelector('#open-cart').addEventListener('click', () => { renderCart(); showDialog(cartDialog); });
document.querySelector('#mobile-cart').addEventListener('click', () => { renderCart(); showDialog(cartDialog); });

document.querySelector('#cart-items').addEventListener('click', (event) => {
	const button = event.target.closest('[data-quantity]');
	if (!button) return;
	const item = cart.items.find((line) => line.name === button.dataset.item);
	item.quantity += Number(button.dataset.quantity);
	cart.items = cart.items.filter((line) => line.quantity > 0);
	if (!cart.items.length) cart.restaurantId = null;
	renderCart();
});

document.querySelector('#checkout-button').addEventListener('click', () => {
	if (!cart.items.length) return;
	document.querySelector('#checkout-total').textContent = formatPrice(cart.items.reduce((total, item) => total + item.price * item.quantity, 3));
	cartDialog.close();
	showDialog(checkoutDialog);
});

document.querySelector('#checkout-form').addEventListener('submit', (event) => {
	event.preventDefault();
	if (!cart.items.length || !cart.restaurantId) return;
	const formData = new FormData(event.currentTarget);
	const orders = readOrders();
	const order = {
		id: `FD-${Date.now().toString().slice(-6)}`,
		restaurantId: cart.restaurantId,
		restaurant: restaurants[cart.restaurantId].name,
		customer: String(formData.get('customer_name')).trim(),
		phone: String(formData.get('phone')).trim(),
		address: String(formData.get('address')).trim(),
		items: cart.items.map(({ name, price, quantity }) => ({ name, price, quantity })),
		total: cart.items.reduce((total, item) => total + item.price * item.quantity, 3),
		status: 'placed',
		courier: null,
		createdAt: new Date().toISOString(),
	};
	orders.unshift(order);
	writeOrders(orders);
	cart.items = [];
	cart.restaurantId = null;
	renderCart();
	event.currentTarget.reset();
	closeDialogs();
	renderCustomerOrders();
	document.querySelector('#order-tracking').scrollIntoView({ behavior: 'smooth', block: 'center' });
});

function renderCustomerOrders() {
	const order = readOrders()[0];
	const tracking = document.querySelector('#order-tracking');
	if (!tracking || !order) return;
	const statusLabels = {
		placed: ['Commande confirmée', 'Envoyée au restaurant', 'state-new'],
		accepted: ['Le restaurant a accepté', 'En préparation', 'state-preparing'],
		ready: ['Votre commande est prête', 'Prête à récupérer', 'state-ready'],
		assigned: ['Un livreur est en route', 'Livreur attribué', 'state-preparing'],
	picked_up: ['Votre commande arrive', 'En livraison', 'state-preparing'],
		delivered: ['Bon appétit !', 'Livrée', 'state-ready'],
		rejected: ['Commande annulée', 'Restaurant indisponible', 'state-late'],
	};
	const [title, status, statusClass] = statusLabels[order.status] || statusLabels.placed;
	document.querySelector('#tracking-title').textContent = `${title} · ${order.id}`;
	document.querySelector('#tracking-detail').textContent = `${order.restaurant} · ${formatPrice(order.total)} · ${order.address}`;
	const statusElement = document.querySelector('#tracking-status');
	statusElement.textContent = status;
	statusElement.className = `state-pill ${statusClass}`;
	tracking.hidden = false;
}

renderCustomerOrders();
window.addEventListener('storage', (event) => {
	if (event.key === orderStorageKey) renderCustomerOrders();
});

document.querySelectorAll('.category-chip').forEach((button) => {
	button.addEventListener('click', () => {
		document.querySelectorAll('.category-chip').forEach((chip) => chip.classList.remove('is-active'));
		button.classList.add('is-active');
		filterRestaurants();
	});
});

document.querySelector('#restaurant-search').addEventListener('input', filterRestaurants);

function filterRestaurants() {
	const category = document.querySelector('.category-chip.is-active').dataset.category;
	const query = document.querySelector('#restaurant-search').value.trim().toLocaleLowerCase('fr');
	let visible = 0;
	document.querySelectorAll('.restaurant-card').forEach((card) => {
		const matchesCategory = category === 'all' || card.dataset.category.split(' ').includes(category);
		const matchesQuery = `${card.dataset.name} ${card.dataset.category}`.toLocaleLowerCase('fr').includes(query);
		card.hidden = !(matchesCategory && matchesQuery);
		if (!card.hidden) visible += 1;
	});
	document.querySelector('#result-count').textContent = `${String(visible).padStart(2, '0')} adresse${visible === 1 ? '' : 's'}`;
	document.querySelector('#empty-state').hidden = visible !== 0;
}

document.addEventListener('keydown', (event) => {
	if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
		event.preventDefault();
		document.querySelector('#restaurant-search').focus();
	}
	if (event.key === 'Escape') backdrop.classList.remove('is-visible');
});
}
