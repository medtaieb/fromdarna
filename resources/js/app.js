import './bootstrap';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const orderStorageKey = 'fromdarna.orders.v1';
const shiftStorageKey = 'fromdarna.courier.available.v1';
const profileStorageKey = 'fromdarna.customer.profile.v1';
const addressStorageKey = 'fromdarna.customer.address.v1';
const cartStorageKey = 'fromdarna.customer.cart.v1';
const languageStorageKey = 'fromdarna.customer.language.v1';
const partnerStoreStorageKey = 'fromdarna.partner.store-open.v1';
const partnerPrepStorageKey = 'fromdarna.partner.prep-minutes.v1';
const partnerMenuStorageKey = 'fromdarna.partner.menu-availability.v1';
const defaultPartnerMenu = { kaskrout_escalope: true, chapati_maison: true, ojja_merguez: false };

const customerTranslations = {
	fr: {
		myOrders: 'Mes commandes', welcomeEyebrow: 'LE GOÛT DU COIN, LIVRÉ CHEZ VOUS', welcomeNote: 'Les bonnes adresses de votre ville,<br>à portée de commande.', localDelivery: 'GROMBALIA · LIVRAISON LOCALE',
		heroKicker: 'GROMBALIA · NOS ADRESSES',
		search: 'Rechercher',
		heroTitle: 'Un bon repas.<br>Tout près.', heroDescription: 'Vos restaurants préférés, sans chercher loin.', exploreRestaurants: 'Explorer les restaurants',
		categoryEyebrow: 'ON A FAIM DE QUOI ?', categoryTitle: 'Choisir une catégorie', seeAll: 'Tout voir', all: 'Tout',
		sandwiches: 'Sandwichs', pizza: 'Pizza', grills: 'Grillades', pastries: 'Pâtisseries', cafes: 'Cafés',
		popularPicks: 'Les incontournables', searchEmpty: 'Aucune adresse ne correspond à cette recherche.', sortPopular: 'Les plus populaires', sortFast: 'Livraison la plus rapide', sortNearby: 'Les plus proches', restaurantMenu: 'MENU DU RESTAURANT',
		yourOrder: 'VOTRE COMMANDE', cart: 'Le panier', subtotal: 'Sous-total', delivery: 'Livraison', estimatedTotal: 'Total estimé',
		cashPayment: 'Paiement en espèces à la livraison', continueOrder: 'Continuer la commande', smallBasketFee: 'Frais petit panier', yourAccount: 'VOTRE COMPTE',
		orderHistory: 'Historique des commandes', close: 'Fermer', lastStep: 'DERNIÈRE ÉTAPE', whereToDeliver: 'Où vous livrer ?',
		yourName: 'Votre nom', phone: 'Téléphone', confirmOrder: 'Confirmer la commande', homeDelivery: 'LIVRAISON À DOMICILE',
		chooseAddress: 'Choisir une adresse', pinInstruction: 'Placez l’épingle sur votre adresse, puis ajoutez un repère pour le livreur.',
		streetAddress: 'Adresse, rue et quartier', landmark: 'Repère pour le livreur', saveAddress: 'Enregistrer cette adresse',
		phoneLogin: 'Connexion par téléphone', continue: 'Continuer', verifyCode: 'Vérifier le code',
		phoneIntro: 'Saisissez votre numéro pour continuer et retrouver vos commandes.', otpInstruction: 'Entrez le code à 6 chiffres associé au numéro',
		outsideZone: 'Adresse hors zone de livraison', outsideZoneDetail: 'Cette adresse ne semble pas couverte par la zone pilote actuelle.',
		editAddress: 'Modifier l’adresse', myLocation: 'Ma position',
	},
	ar: {
		myOrders: 'طلباتي', welcomeEyebrow: 'نكهة من منطقتك، حتى باب دارك', welcomeNote: 'أفضل عناوين منطقتك،<br>على بُعد طلب واحد.', localDelivery: 'قرمبالية · توصيل محلي',
		heroKicker: 'قرمبالية · وجهاتنا',
		search: 'بحث',
		heroTitle: 'وجبة طيبة.<br>قريبة منك.', heroDescription: 'مطاعمك المفضلة على بُعد طلب واحد.', exploreRestaurants: 'اكتشف المطاعم',
		categoryEyebrow: 'شنوة تشتهي اليوم؟', categoryTitle: 'اختر صنفاً', seeAll: 'عرض الكل', all: 'الكل',
		sandwiches: 'سندويتشات', pizza: 'بيتزا', grills: 'مشاوي', pastries: 'حلويات', cafes: 'مقاهي',
		popularPicks: 'الأكثر طلباً', searchEmpty: 'لا توجد مطاعم تطابق بحثك.', sortPopular: 'الأكثر شعبية', sortFast: 'الأسرع توصيلاً', sortNearby: 'الأقرب إليك', restaurantMenu: 'قائمة المطعم',
		yourOrder: 'طلبك', cart: 'السلة', subtotal: 'المجموع الفرعي', delivery: 'التوصيل', estimatedTotal: 'المجموع التقديري',
		cashPayment: 'الدفع نقداً عند الاستلام', continueOrder: 'متابعة الطلب', smallBasketFee: 'رسوم الطلب الصغير', yourAccount: 'حسابك',
		orderHistory: 'سجل الطلبات', close: 'إغلاق', lastStep: 'الخطوة الأخيرة', whereToDeliver: 'أين نوصّل طلبك؟',
		yourName: 'الاسم الكامل', phone: 'رقم الهاتف', confirmOrder: 'تأكيد الطلب', homeDelivery: 'التوصيل إلى المنزل',
		chooseAddress: 'اختر عنواناً', pinInstruction: 'حرّك الدبوس إلى عنوانك وأضف علامة مميزة لمساعدة الموصّل.',
		streetAddress: 'العنوان والشارع والحي', landmark: 'علامة مميزة للموصّل', saveAddress: 'حفظ هذا العنوان',
		phoneLogin: 'الدخول برقم الهاتف', continue: 'متابعة', verifyCode: 'تحقق من الرمز',
		phoneIntro: 'أدخل رقم هاتفك للمتابعة والاطلاع على طلباتك.', otpInstruction: 'أدخل الرمز المكوّن من 6 أرقام المرسل إلى',
		outsideZone: 'العنوان خارج نطاق التوصيل', outsideZoneDetail: 'يبدو أن هذا العنوان خارج منطقة التوصيل التجريبية الحالية.',
		editAddress: 'تعديل العنوان', myLocation: 'موقعي',
	},
};

function currentLanguage() {
	return localStorage.getItem(languageStorageKey) === 'ar' ? 'ar' : 'fr';
}

function translate(key) {
	return customerTranslations[currentLanguage()][key] || customerTranslations.fr[key] || key;
}

function readStoredValue(key, fallback) {
	try {
		const value = localStorage.getItem(key);
		return value ? JSON.parse(value) : fallback;
	} catch {
		return fallback;
	}
}

function readPartnerMenu() {
	return { ...defaultPartnerMenu, ...readStoredValue(partnerMenuStorageKey, {}) };
}

function isPartnerStoreOpen() {
	return localStorage.getItem(partnerStoreStorageKey) !== 'false';
}

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
	const portalNavigation = document.querySelector('.portal-nav');
	portalNavigation?.addEventListener('click', (event) => {
		const selectedLink = event.target.closest('.portal-nav-link');
		portalNavigation.querySelectorAll('.portal-nav-link').forEach((link) => {
			const isSelected = link === selectedLink;
			link.classList.toggle('is-current', isSelected);
			if (isSelected) link.setAttribute('aria-current', 'location');
			else link.removeAttribute('aria-current');
		});
	});

	const orderStatus = {
		placed: ['À confirmer', 'state-new'],
		accepted: ['En préparation', 'state-preparing'],
		ready: ['Prête', 'state-ready'],
		offered: ['Offre envoyée', 'state-new'],
		assigned: ['Livreur attribué', 'state-preparing'],
		picked_up: ['En livraison', 'state-preparing'],
		delivered: ['Livrée', 'state-ready'],
		rejected: ['Refusée', 'state-late'],
	};
	const money = (amount) => `${Number(amount || 0).toFixed(3).replace('.', ',')} TND`;
	const receivedAt = (value) => new Date(value).toLocaleTimeString('fr-TN', { hour: '2-digit', minute: '2-digit' });
	let partnerHistoryView = false;

	function renderPartnerOrders() {
		const orders = readOrders();
		const body = document.querySelector('#partner-orders');
		if (!body) return;
		const partnerOrders = partnerHistoryView
			? orders.filter((order) => ['delivered', 'rejected'].includes(order.status))
			: orders.filter((order) => !['delivered', 'rejected'].includes(order.status));
		const actionable = partnerOrders.filter((order) => ['placed', 'accepted'].includes(order.status)).length;
		body.innerHTML = partnerOrders.map((order) => {
			const [label, className] = orderStatus[order.status] || orderStatus.placed;
			const itemCount = order.items.reduce((total, item) => total + item.quantity, 0);
			const action = order.status === 'placed'
				? `<button class="small-primary" data-order-action="accept" data-order-id="${escapeHtml(order.id)}" type="button">Accepter</button><button class="small-quiet" data-order-action="reject" data-order-id="${escapeHtml(order.id)}" type="button">Refuser</button>`
				: order.status === 'accepted'
					? `<button class="small-primary" data-order-action="ready" data-order-id="${escapeHtml(order.id)}" type="button">Marquer prête</button>`
					: '<span class="muted-action">Aucune action requise</span>';
			return `<tr><td><button class="order-reference" data-partner-order-detail="${escapeHtml(order.id)}" type="button">#${escapeHtml(order.id)}</button><small>${escapeHtml(order.customer)} · ${escapeHtml(order.phone)}</small></td><td>${receivedAt(order.createdAt)}</td><td>${itemCount} article${itemCount > 1 ? 's' : ''}</td><td>${money(order.total)}</td><td><span class="state-pill ${className}">${label}</span></td><td class="row-actions">${action}</td></tr>`;
		}).join('');
		const empty = document.querySelector('#partner-orders-empty');
		empty.hidden = partnerOrders.length > 0;
		empty.textContent = partnerHistoryView ? 'Aucune commande terminée.' : 'Aucune commande à traiter pour le moment.';
		document.querySelector('#partner-order-heading').textContent = partnerHistoryView ? 'Historique des commandes' : 'À traiter maintenant';
		document.querySelector('#partner-order-subheading').textContent = partnerHistoryView ? 'Commandes livrées ou refusées.' : 'Les nouvelles commandes apparaissent ici.';
		document.querySelector('#partner-orders-toggle').innerHTML = `${partnerHistoryView ? 'Commandes à traiter' : 'Voir l’historique'} <span>→</span>`;
		document.querySelector('#partner-orders-received').textContent = orders.filter((order) => order.status !== 'rejected').length;
		document.querySelector('#partner-pending-count').textContent = actionable;
		document.querySelector('#partner-total-to-settle').innerHTML = `${money(partnerOrders.reduce((total, order) => total + order.total, 0)).replace(' TND', '')} <em>TND</em>`;
	}

	function renderPartnerSettings() {
		const open = localStorage.getItem(partnerStoreStorageKey) !== 'false';
		const prep = localStorage.getItem(partnerPrepStorageKey) || '15';
		const menu = readPartnerMenu();
		const storeStatus = document.querySelector('.open-state');
		if (storeStatus) {
			storeStatus.innerHTML = `<i></i>${open ? 'Ouvert' : 'En pause'}`;
			storeStatus.classList.toggle('is-paused', !open);
			document.querySelector('[data-toggle-store]').textContent = open ? 'Mettre en pause' : 'Reprendre les commandes';
		}
		const prepSelect = document.querySelector('#partner-prep-minutes');
		if (prepSelect) {
			prepSelect.value = prep;
			document.querySelector('#partner-prep-label').textContent = `${prep} min`;
		}
		document.querySelectorAll('[data-product-availability]').forEach((input) => {
			const available = Boolean(menu[input.dataset.productAvailability]);
			input.checked = available;
			input.closest('.availability-toggle').querySelector('span').textContent = available ? 'En vente' : 'En pause';
			document.querySelector(`[data-product-dot="${input.dataset.productAvailability}"]`).className = `availability-dot ${available ? 'available' : 'unavailable'}`;
		});
	}

	function renderOpsOrders() {
		const body = document.querySelector('#ops-orders');
		if (!body) return;
		const allOrders = readOrders();
		const selectedZone = document.querySelector('#ops-zone-filter')?.value || 'all';
		const selectedPeriod = document.querySelector('#ops-time-filter')?.value || 'all';
		const now = Date.now();
		const scopedOrders = allOrders.filter((order) => {
			const townMatches = selectedZone === 'all' || (order.town || 'Grombalia') === selectedZone;
			const age = now - new Date(order.createdAt).getTime();
			const periodMatches = selectedPeriod === 'all' || (selectedPeriod === 'today'
				? new Date(order.createdAt).toDateString() === new Date().toDateString()
				: age <= 7 * 24 * 60 * 60 * 1000);
			return townMatches && periodMatches;
		});
		const activeOrders = scopedOrders.filter((order) => !['delivered', 'rejected'].includes(order.status));
		const completedOrders = scopedOrders.filter((order) => ['delivered', 'rejected'].includes(order.status));
		const unassigned = activeOrders.filter((order) => ['placed', 'ready'].includes(order.status));
		const lateOrders = activeOrders.filter((order) => Date.now() - new Date(order.createdAt).getTime() > 40 * 60 * 1000);
		const currentFilter = document.querySelector('.filter-pill.is-selected')?.dataset.opsFilter || 'all';
		const visibleOrders = currentFilter === 'completed'
			? completedOrders
			: currentFilter === 'unassigned'
			? activeOrders.filter((order) => ['placed', 'ready'].includes(order.status))
			: currentFilter === 'late' ? lateOrders : activeOrders;
		body.innerHTML = visibleOrders.map((order) => {
			const [label, className] = orderStatus[order.status] || orderStatus.placed;
			const incidentLabel = order.incidentReport ? `<span class="state-pill state-late">Incident : ${escapeHtml(order.incidentReport.type)}</span>` : '';
			const courierCell = order.courier
				? escapeHtml(order.courier)
				: `<select aria-label="Choisir un livreur" data-courier-select="${escapeHtml(order.id)}"><option value="">Sélectionner</option><option>Livreur Grombalia 1</option><option>Livreur Grombalia 2</option><option>Livreur Grombalia 3</option></select>`;
			const action = order.courier
				? `<button class="small-quiet" data-ops-detail="${escapeHtml(order.id)}" type="button">Détails</button>`
				: `<button class="small-primary" data-assign-order="${escapeHtml(order.id)}" type="button">Attribuer</button><button class="small-quiet" data-ops-detail="${escapeHtml(order.id)}" type="button">Détails</button>`;
			return `<tr><td><strong>#${escapeHtml(order.id)}</strong><small>${money(order.total)} · cash</small></td><td>${escapeHtml(order.restaurant)}</td><td>${escapeHtml(order.address)}<small>${escapeHtml(order.customer)}</small></td><td>${receivedAt(order.createdAt)}</td><td><span class="state-pill ${className}">${label}</span>${incidentLabel}</td><td>${courierCell}</td><td class="ops-row-actions">${action}</td></tr>`;
		}).join('');
		document.querySelector('#ops-orders-empty').hidden = visibleOrders.length > 0;
		document.querySelector('#ops-total-count').textContent = activeOrders.length;
		document.querySelector('#ops-unassigned-count').textContent = unassigned.length;
		document.querySelector('#ops-late-count').textContent = lateOrders.length;
		document.querySelector('#ops-completed-count').textContent = completedOrders.length;
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
		const courierOrders = readOrders().filter((order) => order.courier && ['offered', 'assigned', 'picked_up'].includes(order.status));
		container.innerHTML = courierOrders.length ? courierOrders.map((order) => {
			const isOffer = order.status === 'offered';
			const onRoute = order.status === 'picked_up';
			const actions = isOffer
				? `<div class="courier-job-actions"><button class="small-primary" data-courier-accept-order="${escapeHtml(order.id)}" type="button">Accepter la course</button><button class="small-quiet" data-courier-decline-order="${escapeHtml(order.id)}" type="button">Refuser</button></div>`
				: onRoute
					? `<button class="delivery-action" data-courier-deliver-order="${escapeHtml(order.id)}" type="button">Confirmer la livraison <span>→</span></button>`
					: `<button class="delivery-action" data-courier-pickup-order="${escapeHtml(order.id)}" type="button">Confirmer le retrait <span>→</span></button>`;
			const incident = order.incidentReport ? `<p class="courier-incident-saved">Incident signalé : ${escapeHtml(order.incidentReport.type)}</p>` : '';
			return `<article class="portal-panel courier-job"><div class="panel-heading"><div><p class="eyebrow">${isOffer ? 'NOUVELLE OFFRE' : onRoute ? 'EN LIVRAISON' : 'COURSE À RÉCUPÉRER'} · #${escapeHtml(order.id)}</p><h2>${escapeHtml(order.restaurant)}</h2></div><span class="state-pill ${isOffer ? 'state-new' : onRoute ? 'state-preparing' : 'state-ready'}">${isOffer ? 'Offre reçue' : onRoute ? 'En livraison' : 'À récupérer'}</span></div><div class="route-stops"><div class="route-stop"><span class="route-marker pickup-marker">1</span><div><small>RETRAIT</small><strong>${escapeHtml(order.restaurant)}</strong><p>Commande pour ${escapeHtml(order.customer)}</p></div><a href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(order.restaurant)}" target="_blank" rel="noreferrer" aria-label="Naviguer vers le restaurant">↗</a></div><span class="route-connector"></span><div class="route-stop"><span class="route-marker drop-marker">2</span><div><small>LIVRAISON</small><strong>${escapeHtml(order.address)}</strong><p>${escapeHtml(order.phone)}</p></div><a href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(order.address)}" target="_blank" rel="noreferrer" aria-label="Naviguer vers le client">↗</a></div></div><div class="delivery-order"><span><small>À encaisser en espèces</small><strong>${money(order.total)}</strong></span><span><small>Gain estimé</small><strong>${money(order.courierEarning ?? 3)}</strong></span></div>${actions}<button class="courier-incident-button" data-courier-incident-order="${escapeHtml(order.id)}" type="button">Signaler un incident</button>${incident}</article>`;
		}).join('') : '<p class="portal-empty">Aucune course ne vous est attribuée pour le moment.</p>';
		container.querySelectorAll('[data-courier-accept-order]').forEach((button) => { button.disabled = !available; });
		const allOrders = readOrders();
		const delivered = allOrders.filter((order) => order.courier && order.status === 'delivered');
		const history = document.querySelector('#courier-history');
		if (history) {
			history.innerHTML = delivered.length ? delivered.map((order) => `<div class="history-row"><strong>#${escapeHtml(order.id)}</strong><span>${escapeHtml(order.restaurant)} · ${receivedAt(order.createdAt)}</span><span>Livrée</span><strong>Gain ${money(order.courierEarning ?? 3)}</strong></div>`).join('') : '<p class="portal-empty">Aucune course terminée.</p>';
		}
		document.querySelector('#courier-delivered-count').textContent = delivered.length;
		document.querySelector('#courier-earnings').innerHTML = `${money(delivered.reduce((total, order) => total + (order.courierEarning ?? 3), 0)).replace(' TND', '')} <em>TND</em>`;
		const cashHeld = allOrders.filter((order) => order.courier && ['picked_up', 'delivered'].includes(order.status) && !order.cashRemitted).reduce((total, order) => total + order.total, 0);
		document.querySelector('#courier-cash-held').innerHTML = `${money(cashHeld).replace(' TND', '')} <em>TND</em>`;
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
		const courierAccept = event.target.closest('[data-courier-accept-order]');
		if (courierAccept) {
			if (localStorage.getItem(shiftStorageKey) === 'false') return;
			updateOrder(courierAccept.dataset.courierAcceptOrder, { status: 'assigned', courierAcceptedAt: new Date().toISOString() });
			renderPortalOrders();
			return;
		}
		const courierDecline = event.target.closest('[data-courier-decline-order]');
		if (courierDecline) {
			updateOrder(courierDecline.dataset.courierDeclineOrder, { status: 'ready', courier: null, courierDeclinedAt: new Date().toISOString() });
			renderPortalOrders();
			return;
		}
		const pickupButton = event.target.closest('[data-courier-pickup-order]');
		if (pickupButton) {
			updateOrder(pickupButton.dataset.courierPickupOrder, { status: 'picked_up', pickedUpAt: new Date().toISOString() });
			renderPortalOrders();
			return;
		}
		const deliverButton = event.target.closest('[data-courier-deliver-order]');
		if (deliverButton) {
			const form = document.querySelector('#courier-delivery-form');
			form.reset();
			form.elements.order_id.value = deliverButton.dataset.courierDeliverOrder;
			form.elements.otp.value = '';
			document.querySelector('#courier-delivery-error').hidden = true;
			document.querySelector('#courier-delivery-dialog').showModal();
			return;
		}
		const incidentButton = event.target.closest('[data-courier-incident-order]');
		if (incidentButton) {
			const form = document.querySelector('#courier-incident-form');
			form.reset();
			form.elements.order_id.value = incidentButton.dataset.courierIncidentOrder;
			document.querySelector('#courier-incident-dialog').showModal();
			return;
		}
		const actionButton = event.target.closest('[data-order-action]');
		if (actionButton) {
			if (actionButton.dataset.orderAction === 'reject') {
				const form = document.querySelector('#partner-reject-form');
				form.elements.order_id.value = actionButton.dataset.orderId;
				document.querySelector('#partner-reject-dialog').showModal();
				return;
			}
			const changes = actionButton.dataset.orderAction === 'accept'
				? { status: 'accepted', prepMinutes: Number(localStorage.getItem(partnerPrepStorageKey) || 15) }
				: { status: 'ready' };
			updateOrder(actionButton.dataset.orderId, changes);
			renderPortalOrders();
			return;
		}
		const detailButton = event.target.closest('[data-partner-order-detail]');
		if (detailButton) {
			const order = readOrders().find((item) => item.id === detailButton.dataset.partnerOrderDetail);
			if (!order) return;
			const labels = { placed: 'À confirmer', accepted: 'En préparation', ready: 'Prête', assigned: 'Livreur attribué', picked_up: 'En livraison', delivered: 'Livrée', rejected: 'Refusée' };
			document.querySelector('#partner-order-detail-title').textContent = `Commande #${order.id}`;
			document.querySelector('#partner-order-detail-content').innerHTML = `<div class="partner-detail-status"><span class="state-pill">${labels[order.status] || 'À confirmer'}</span><strong>${money(order.total)}</strong></div>${order.prepMinutes ? `<p class="prep-order-time">Temps annoncé : ${order.prepMinutes} min</p>` : ''}<div class="partner-detail-list">${order.items.map((item) => `<div><span>${item.quantity} × ${escapeHtml(item.name)}${item.note ? `<small>${escapeHtml(item.note)}</small>` : ''}</span><strong>${money(item.price * item.quantity)}</strong></div>`).join('')}</div><div class="partner-detail-address"><small>CLIENT</small><strong>${escapeHtml(order.customer)} · ${escapeHtml(order.phone)}</strong><span>${escapeHtml(order.address)}${order.landmark ? ` · ${escapeHtml(order.landmark)}` : ''}</span></div>${order.rejectionReason ? `<div class="partner-rejection-note">Motif du refus : ${escapeHtml(order.rejectionReason)}${order.rejectionNote ? ` · ${escapeHtml(order.rejectionNote)}` : ''}</div>` : ''}`;
			document.querySelector('#partner-order-dialog').showModal();
			return;
		}
		if (event.target.closest('[data-close-partner-dialog]')) {
			document.querySelectorAll('.partner-dialog[open]').forEach((dialog) => dialog.close());
		}
		if (event.target.closest('[data-close-courier-dialog]')) {
			document.querySelectorAll('#courier-delivery-dialog[open], #courier-incident-dialog[open]').forEach((dialog) => dialog.close());
		}
		const assignButton = event.target.closest('[data-assign-order]');
		if (assignButton) {
			const select = document.querySelector(`[data-courier-select="${CSS.escape(assignButton.dataset.assignOrder)}"]`);
			if (!select?.value) {
				select?.focus();
				return;
			}
			updateOrder(assignButton.dataset.assignOrder, { courier: select.value, status: 'offered', courierOfferedAt: new Date().toISOString() });
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
		const open = localStorage.getItem(partnerStoreStorageKey) !== 'false';
		localStorage.setItem(partnerStoreStorageKey, String(!open));
		renderPartnerSettings();
	});

	document.querySelector('#partner-prep-minutes')?.addEventListener('change', (event) => {
		localStorage.setItem(partnerPrepStorageKey, event.currentTarget.value);
		renderPartnerSettings();
	});

	document.querySelectorAll('[data-product-availability]').forEach((input) => {
		input.addEventListener('change', () => {
			const menu = readPartnerMenu();
			menu[input.dataset.productAvailability] = input.checked;
			localStorage.setItem(partnerMenuStorageKey, JSON.stringify(menu));
			renderPartnerSettings();
		});
	});

	document.querySelector('#partner-orders-toggle')?.addEventListener('click', () => {
		partnerHistoryView = !partnerHistoryView;
		renderPartnerOrders();
	});

	document.querySelector('#partner-reject-form')?.addEventListener('submit', (event) => {
		event.preventDefault();
		const form = event.currentTarget;
		updateOrder(form.elements.order_id.value, {
			status: 'rejected',
			rejectionReason: form.elements.reason.value,
			rejectionNote: form.elements.note.value.trim(),
		});
		form.reset();
		document.querySelector('#partner-reject-dialog').close();
		renderPortalOrders();
	});

	document.querySelector('#courier-delivery-form')?.addEventListener('submit', (event) => {
		event.preventDefault();
		const form = event.currentTarget;
		const order = readOrders().find((item) => item.id === form.elements.order_id.value);
		if (!order || String(form.elements.otp.value).trim() !== String(order.deliveryCode)) {
			document.querySelector('#courier-delivery-error').hidden = false;
			return;
		}
		updateOrder(order.id, { status: 'delivered', deliveredAt: new Date().toISOString(), courierEarning: order.courierEarning ?? 3 });
		document.querySelector('#courier-delivery-dialog').close();
		renderPortalOrders();
	});

	document.querySelector('#courier-delivery-form [name="otp"]')?.addEventListener('input', () => {
		document.querySelector('#courier-delivery-error').hidden = true;
	});

	document.querySelector('#courier-incident-form')?.addEventListener('submit', (event) => {
		event.preventDefault();
		const form = event.currentTarget;
		updateOrder(form.elements.order_id.value, {
			incidentReport: { type: form.elements.type.value, note: form.elements.note.value.trim(), createdAt: new Date().toISOString() },
		});
		document.querySelector('#courier-incident-dialog').close();
		renderPortalOrders();
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
	renderPartnerSettings();
	window.addEventListener('storage', (event) => {
		if ([orderStorageKey, shiftStorageKey, partnerStoreStorageKey, partnerPrepStorageKey, partnerMenuStorageKey].includes(event.key)) {
			renderPortalOrders();
			renderPartnerSettings();
		}
	});
}

const restaurants = {
	dar: {
		name: 'Dar El Kaskrout',
		items: [
			{ productId: 'kaskrout_escalope', name: 'Kaskrout escalope', description: 'Escalope grillée, salade méchouia, harissa et frites.', price: 8.5, choices: [{ label: 'Harissa', options: [{ name: 'Douce', price: 0 }, { name: 'Moyenne', price: 0 }, { name: 'Forte', price: 0 }] }], extras: [{ name: 'Supplément fromage', price: 1.5 }] },
			{ productId: 'chapati_maison', name: 'Chapati maison', description: 'Œuf, fromage, thon et pommes de terre.', price: 7, choices: [{ label: 'Fromage', options: [{ name: 'Classique', price: 0 }, { name: 'Double fromage', price: 1.5 }] }] },
			{ productId: 'ojja_merguez', name: 'Ojja merguez', description: 'Merguez, tomate, poivron et œufs.', price: 13, choices: [{ label: 'Piquant', options: [{ name: 'Doux', price: 0 }, { name: 'Relevé', price: 0 }] }] },
		],
	},
	piazza: {
		name: 'La Piazza Grombalia',
		items: [
			{ name: 'Pizza Margherita', description: 'Tomate, mozzarella, basilic frais.', price: 16, choices: [{ label: 'Taille', options: [{ name: 'Petite', price: 0 }, { name: 'Grande', price: 6 }] }], extras: [{ name: 'Extra mozzarella', price: 2 }] },
			{ name: 'Pizza Tunisienne', description: 'Merguez, poivron, olives et œuf.', price: 21, choices: [{ label: 'Taille', options: [{ name: 'Petite', price: 0 }, { name: 'Grande', price: 7 }] }] },
			{ name: 'Penne arrabbiata', description: 'Sauce tomate relevée, parmesan.', price: 17, extras: [{ name: 'Extra parmesan', price: 1.5 }] },
		],
	},
	mechoui: {
		name: 'Le Mechoui du Cap Bon',
		items: [
			{ name: 'Poulet grillé', description: 'Poulet rôti, salade du jour et frites.', price: 19, choices: [{ label: 'Accompagnement', options: [{ name: 'Frites', price: 0 }, { name: 'Salade', price: 0 }] }] },
			{ name: 'Ojja merguez', description: 'Merguez, tomate, poivron et œufs.', price: 15, choices: [{ label: 'Piquant', options: [{ name: 'Doux', price: 0 }, { name: 'Relevé', price: 0 }] }] },
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

const storedCart = readStoredValue(cartStorageKey, { restaurantId: null, items: [] });
const cart = { restaurantId: storedCart.restaurantId || null, items: Array.isArray(storedCart.items) ? storedCart.items : [] };
const menuDialog = document.querySelector('#menu-dialog');
const cartDialog = document.querySelector('#cart-dialog');
const checkoutDialog = document.querySelector('#checkout-dialog');
const addressDialog = document.querySelector('#address-dialog');
const accountDialog = document.querySelector('#account-dialog');
const orderDetailDialog = document.querySelector('#order-detail-dialog');
const backdrop = document.querySelector('#dialog-backdrop');
const formatPrice = (price) => `${price.toFixed(3).replace('.', ',')} TND`;
const pilotCenter = { lat: 36.6042, lng: 10.5031 };
const pilotRadiusKm = 3;
let addressMap = null;
let addressMarker = null;
let selectedCoordinates = { ...pilotCenter };
let resumeCheckoutAfterAddress = false;
let resumeCheckoutAfterLogin = false;
let resumeHistoryAfterLogin = false;

function saveCart() {
	localStorage.setItem(cartStorageKey, JSON.stringify(cart));
}
function showDialog(dialog) {
	backdrop.classList.add('is-visible');
	dialog.showModal();
}

function closeDialogs() {
	menuDialog.close();
	cartDialog.close();
	checkoutDialog.close();
	addressDialog.close();
	accountDialog.close();
	orderDetailDialog.close();
	backdrop.classList.remove('is-visible');
}

function getCustomerProfile() {
	return readStoredValue(profileStorageKey, null);
}

function getDeliveryAddress() {
	return readStoredValue(addressStorageKey, null);
}

function distanceFromPilotCenter(lat, lng) {
	const radians = (degrees) => degrees * Math.PI / 180;
	const latDistance = radians(lat - pilotCenter.lat);
	const lngDistance = radians(lng - pilotCenter.lng);
	const haversine = Math.sin(latDistance / 2) ** 2
		+ Math.cos(radians(pilotCenter.lat)) * Math.cos(radians(lat)) * Math.sin(lngDistance / 2) ** 2;
	return 6371 * 2 * Math.atan2(Math.sqrt(haversine), Math.sqrt(1 - haversine));
}

function updateCoverageStatus(lat, lng) {
	selectedCoordinates = { lat, lng };
	const covered = distanceFromPilotCenter(lat, lng) <= pilotRadiusKm;
	const status = document.querySelector('#coverage-status');
	status.classList.toggle('is-covered', covered);
	status.classList.toggle('is-outside', !covered);
	status.querySelector('span').textContent = covered
		? (currentLanguage() === 'ar' ? 'ضمن نطاق قرمبالية التجريبي التقريبي.' : 'Dans la zone pilote indicative de Grombalia.')
		: (currentLanguage() === 'ar' ? 'يبدو أن العنوان خارج نطاق قرمبالية التجريبي.' : 'Cette adresse semble hors de la zone pilote de Grombalia.');
	return covered;
}

function initializeAddressMap() {
	if (!addressMap) {
		addressMap = L.map('address-map', { scrollWheelZoom: false }).setView([selectedCoordinates.lat, selectedCoordinates.lng], 15);
		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			maxZoom: 19,
			attribution: '&copy; OpenStreetMap contributors',
		}).addTo(addressMap);
		addressMarker = L.marker([selectedCoordinates.lat, selectedCoordinates.lng], {
			draggable: true,
			icon: L.divIcon({ className: 'customer-map-pin', html: '<span></span>', iconSize: [26, 34], iconAnchor: [13, 30] }),
		}).addTo(addressMap);
		addressMarker.on('dragend', () => {
			const point = addressMarker.getLatLng();
			updateCoverageStatus(point.lat, point.lng);
		});
		addressMap.on('click', (event) => {
			addressMarker.setLatLng(event.latlng);
			updateCoverageStatus(event.latlng.lat, event.latlng.lng);
		});
	}
	const saved = getDeliveryAddress();
	if (saved?.coordinates) selectedCoordinates = saved.coordinates;
	addressMarker.setLatLng([selectedCoordinates.lat, selectedCoordinates.lng]);
	addressMap.setView([selectedCoordinates.lat, selectedCoordinates.lng], 15);
	updateCoverageStatus(selectedCoordinates.lat, selectedCoordinates.lng);
	window.setTimeout(() => addressMap.invalidateSize(), 50);
}

function openAddressDialog({ resumeCheckout = false } = {}) {
	resumeCheckoutAfterAddress = resumeCheckout;
	const saved = getDeliveryAddress();
	const form = document.querySelector('#address-form');
	form.elements.street.value = saved?.street || '';
	form.elements.landmark.value = saved?.landmark || '';
	if (checkoutDialog.open) checkoutDialog.close();
	showDialog(addressDialog);
	window.setTimeout(initializeAddressMap, 0);
}

function renderCustomerHeader() {
	const profile = getCustomerProfile();
	const address = getDeliveryAddress();
	document.querySelector('#account-label').textContent = profile ? (currentLanguage() === 'ar' ? 'حسابي' : 'Mon compte') : (currentLanguage() === 'ar' ? 'دخول' : 'Connexion');
	document.querySelector('#location-label').innerHTML = address
		? `${escapeHtml(address.street)} <span aria-hidden="true">⌄</span>`
		: `${currentLanguage() === 'ar' ? 'اختر عنواناً' : 'Choisir une adresse'} <span aria-hidden="true">⌄</span>`;
	const checkoutAddress = document.querySelector('#checkout-address-label');
	if (checkoutAddress) checkoutAddress.textContent = address ? [address.street, address.landmark].filter(Boolean).join(' · ') : 'Aucune adresse enregistrée';
	const outsideBanner = document.querySelector('#outside-zone');
	if (outsideBanner) outsideBanner.hidden = !address || address.covered;
	const savedProfile = profile;
	const nameInput = document.querySelector('#checkout-form [name="customer_name"]');
	const phoneInput = document.querySelector('#checkout-form [name="phone"]');
	if (savedProfile && nameInput && !nameInput.value) nameInput.value = savedProfile.name || '';
	if (savedProfile && phoneInput && !phoneInput.value) phoneInput.value = savedProfile.phone || '';
	document.querySelector('#checkout-address-value').value = address ? [address.street, address.landmark].filter(Boolean).join(', ') : '';
}

function continueCheckout() {
	const address = getDeliveryAddress();
	if (!getCustomerProfile()) {
		resumeCheckoutAfterLogin = true;
		openAccountDialog();
		return;
	}
	if (!address || !address.covered) {
		openAddressDialog({ resumeCheckout: true });
		return;
	}
	renderCustomerHeader();
	const subtotal = cart.items.reduce((total, item) => total + item.price * item.quantity, 0);
	document.querySelector('#checkout-total').textContent = formatPrice(subtotal + 3 + (subtotal < 10 ? 1 : 0));
	showDialog(checkoutDialog);
}

function openAccountDialog() {
	const profile = getCustomerProfile();
	const signedIn = document.querySelector('#signed-in-account');
	const phoneForm = document.querySelector('#phone-form');
	const otpForm = document.querySelector('#otp-form');
	signedIn.hidden = !profile;
	phoneForm.hidden = Boolean(profile);
	otpForm.hidden = true;
	if (profile) document.querySelector('#signed-in-phone').textContent = profile.phone;
	else phoneForm.elements.phone.value = readOrders()[0]?.phone || '';
	showDialog(accountDialog);
}

function openOrderHistory() {
	if (!getCustomerProfile()) {
		resumeHistoryAfterLogin = true;
		openAccountDialog();
		return;
	}
	renderOrderHistory();
	const section = document.querySelector('#customer-orders');
	section.hidden = false;
	section.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function applyCustomerLanguage() {
	const language = currentLanguage();
	document.documentElement.lang = language;
	document.documentElement.dir = language === 'ar' ? 'rtl' : 'ltr';
	document.body.classList.toggle('is-rtl', language === 'ar');
	document.querySelectorAll('[data-i18n]').forEach((element) => {
		element.innerHTML = customerTranslations[language][element.dataset.i18n] || customerTranslations.fr[element.dataset.i18n] || '';
	});
	document.querySelector('#restaurant-search').placeholder = language === 'ar' ? 'ابحث عن مطعم أو طبق...' : 'Restaurant, plat, envie...';
	document.querySelector('#language-toggle').textContent = language === 'ar' ? 'Français' : 'عربي';
	const locationLabel = getDeliveryAddress()?.street || (language === 'ar' ? 'اختر عنواناً' : 'Choisir une adresse');
	document.querySelector('#location-label').innerHTML = `${escapeHtml(locationLabel)} <span aria-hidden="true">⌄</span>`;
	if (!document.querySelector('#customer-orders').hidden) renderOrderHistory();
	renderCustomerOrders();
}

document.querySelector('#language-toggle')?.addEventListener('click', () => {
	localStorage.setItem(languageStorageKey, currentLanguage() === 'fr' ? 'ar' : 'fr');
	applyCustomerLanguage();
	renderCustomerHeader();
});

function renderOrderHistory() {
	const container = document.querySelector('#order-history-list');
	const orders = readOrders();
	container.innerHTML = orders.length ? orders.map((order) => {
		const labels = currentLanguage() === 'ar'
			? { placed: 'مؤكد', accepted: 'قيد التحضير', ready: 'جاهز', offered: 'عرض توصيل', assigned: 'تم تعيين موصّل', picked_up: 'قيد التوصيل', delivered: 'تم التوصيل', rejected: 'ملغى' }
			: { placed: 'Confirmée', accepted: 'En préparation', ready: 'Prête', offered: 'Offre envoyée', assigned: 'Livreur attribué', picked_up: 'En livraison', delivered: 'Livrée', rejected: 'Annulée' };
		const language = currentLanguage() === 'ar' ? 'ar-TN' : 'fr-TN';
		const itemCount = order.items.reduce((total, item) => total + item.quantity, 0);
		return `<article class="history-order"><div><small>${new Date(order.createdAt).toLocaleString(language, { dateStyle: 'medium', timeStyle: 'short' })} · #${escapeHtml(order.id)}</small><h3>${escapeHtml(order.restaurant)}</h3><span>${itemCount} ${currentLanguage() === 'ar' ? 'عنصر' : 'article(s)'} · ${formatPrice(order.total)}</span></div><div class="history-order-actions"><span class="state-pill">${labels[order.status] || labels.placed}</span><button class="small-quiet" type="button" data-order-detail="${escapeHtml(order.id)}">${currentLanguage() === 'ar' ? 'التفاصيل' : 'Détails'}</button></div></article>`;
	}).join('') : '<p class="history-empty">Vos commandes apparaîtront ici.</p>';
}

function openOrderDetail(orderId) {
	const order = readOrders().find((item) => item.id === orderId);
	if (!order) return;
	const labels = currentLanguage() === 'ar'
		? { placed: 'أُرسل إلى المطعم', accepted: 'قيد التحضير', ready: 'جاهز', offered: 'تم إرسال عرض توصيل', assigned: 'تم تعيين موصّل', picked_up: 'قيد التوصيل', delivered: 'تم التوصيل', rejected: 'ملغى' }
		: { placed: 'Envoyée au restaurant', accepted: 'En préparation', ready: 'Prête', offered: 'Offre envoyée au livreur', assigned: 'Livreur attribué', picked_up: 'En livraison', delivered: 'Livrée', rejected: 'Annulée' };
	const steps = [
		...(currentLanguage() === 'ar'
			? [['placed', 'تم تأكيد الطلب'], ['accepted', 'قبل المطعم طلبك'], ['ready', 'جاهز للاستلام'], ['offered', 'تم إرسال عرض توصيل'], ['assigned', 'تم قبول التوصيل'], ['picked_up', 'قيد التوصيل'], ['delivered', 'تم التوصيل']]
			: [['placed', 'Commande confirmée'], ['accepted', 'Acceptée par le restaurant'], ['ready', 'Prête à récupérer'], ['offered', 'Offre envoyée au livreur'], ['assigned', 'Course acceptée'], ['picked_up', 'En livraison'], ['delivered', 'Livrée']]),
	];
	const currentIndex = steps.findIndex(([status]) => status === order.status);
	const itemRows = order.items.map((item) => `<div class="detail-item"><span>${item.quantity} × ${escapeHtml(item.name)}${item.note ? `<small>${escapeHtml(item.note)}</small>` : ''}</span><strong>${formatPrice(item.price * item.quantity)}</strong></div>`).join('');
	document.querySelector('#order-detail-title').textContent = `Commande #${order.id}`;
	document.querySelector('#order-detail-content').innerHTML = `
		<div class="detail-status"><span class="state-pill">${labels[order.status] || 'Confirmée'}</span><strong>${formatPrice(order.total)}</strong></div>
		<div class="order-timeline">${steps.map(([status, title], index) => `<div class="timeline-step ${index <= currentIndex ? 'is-complete' : ''} ${status === order.status ? 'is-current' : ''}"><i></i><span>${title}</span></div>`).join('')}</div>
		<div class="detail-items">${itemRows}</div>
		<div class="detail-address"><small>${currentLanguage() === 'ar' ? 'عنوان التوصيل' : 'LIVRER À'}</small><strong>${escapeHtml(order.address)}</strong>${order.landmark ? `<span>${escapeHtml(order.landmark)}</span>` : ''}</div>
		${order.deliveryCode && !['delivered', 'rejected'].includes(order.status) ? `<div class="customer-delivery-code"><small>${currentLanguage() === 'ar' ? 'رمز تأكيد التسليم' : 'CODE À COMMUNIQUER AU LIVREUR'}</small><strong>${escapeHtml(order.deliveryCode)}</strong></div>` : ''}
		<div class="detail-total"><span>${currentLanguage() === 'ar' ? 'الدفع نقداً' : 'Paiement en espèces'}</span><strong>${formatPrice(order.total)}</strong></div>`;
	showDialog(orderDetailDialog);
}

document.querySelector('#address-form')?.addEventListener('submit', (event) => {
	event.preventDefault();
	const form = event.currentTarget;
	const covered = distanceFromPilotCenter(selectedCoordinates.lat, selectedCoordinates.lng) <= pilotRadiusKm;
	const address = {
		street: String(form.elements.street.value).trim(),
		landmark: String(form.elements.landmark.value).trim(),
		coordinates: { ...selectedCoordinates },
		covered,
	};
	localStorage.setItem(addressStorageKey, JSON.stringify(address));
	closeDialogs();
	renderCustomerHeader();
	if (resumeCheckoutAfterAddress) {
		resumeCheckoutAfterAddress = false;
		if (covered) continueCheckout();
		else document.querySelector('#outside-zone').scrollIntoView({ behavior: 'smooth', block: 'center' });
	}
});

document.querySelector('#locate-me')?.addEventListener('click', () => {
	if (!navigator.geolocation) return;
	navigator.geolocation.getCurrentPosition(({ coords }) => {
		addressMarker.setLatLng([coords.latitude, coords.longitude]);
		addressMap.setView([coords.latitude, coords.longitude], 16);
		updateCoverageStatus(coords.latitude, coords.longitude);
	}, () => {
		document.querySelector('#coverage-status span').textContent = currentLanguage() === 'ar'
			? 'تعذر تحديد الموقع. حرّك الدبوس على الخريطة.'
			: 'Position indisponible. Déplacez l’épingle sur la carte.';
	}, { enableHighAccuracy: true, timeout: 8000 });
});

document.querySelector('#phone-form')?.addEventListener('submit', (event) => {
	event.preventDefault();
	const phone = String(event.currentTarget.elements.phone.value).trim();
	if (phone.length < 8) return;
	document.querySelector('#otp-phone-label').textContent = phone;
	document.querySelector('#otp-hint').textContent = 'Le format du code est vérifié ici. L’envoi et la validation SMS réels dépendent du service d’authentification.';
	event.currentTarget.hidden = true;
	document.querySelector('#otp-form').hidden = false;
	document.querySelector('#otp-form').elements.otp.focus();
});

document.querySelector('#otp-form')?.addEventListener('submit', (event) => {
	event.preventDefault();
	const code = String(event.currentTarget.elements.otp.value).trim();
	if (!/^\d{6}$/.test(code)) {
		event.currentTarget.elements.otp.setCustomValidity('Saisissez un code de 6 chiffres.');
		event.currentTarget.elements.otp.reportValidity();
		return;
	}
	const phone = document.querySelector('#otp-phone-label').textContent;
	const profile = { phone, name: readOrders()[0]?.customer || '' };
	localStorage.setItem(profileStorageKey, JSON.stringify(profile));
	event.currentTarget.reset();
	closeDialogs();
	renderCustomerHeader();
	if (resumeCheckoutAfterLogin) {
		resumeCheckoutAfterLogin = false;
		continueCheckout();
	} else if (resumeHistoryAfterLogin) {
		resumeHistoryAfterLogin = false;
		openOrderHistory();
	}
});

document.querySelector('#otp-form [name="otp"]')?.addEventListener('input', (event) => event.currentTarget.setCustomValidity(''));
document.querySelector('#edit-phone')?.addEventListener('click', () => {
	document.querySelector('#otp-form').hidden = true;
	document.querySelector('#phone-form').hidden = false;
});
document.querySelector('#sign-out')?.addEventListener('click', () => {
	localStorage.removeItem(profileStorageKey);
	closeDialogs();
	renderCustomerHeader();
});

document.querySelector('#order-history-list')?.addEventListener('click', (event) => {
	const button = event.target.closest('[data-order-detail]');
	if (button) openOrderDetail(button.dataset.orderDetail);
});

function openMenu(restaurantId) {
	const restaurant = restaurants[restaurantId];
	menuDialog.dataset.restaurantId = restaurantId;
	document.querySelector('#menu-title').textContent = restaurant.name;
	if (restaurantId === 'dar' && !isPartnerStoreOpen()) {
		document.querySelector('#menu-items').innerHTML = '<p class="menu-unavailable">Le restaurant est temporairement en pause.</p>';
		showDialog(menuDialog);
		return;
	}
	const partnerMenu = readPartnerMenu();
	document.querySelector('#menu-items').innerHTML = restaurant.items.map((item, index) => {
		const choiceFields = (item.choices || []).map((group, groupIndex) => `
			<label class="menu-option">${escapeHtml(group.label)}<select data-choice-index="${groupIndex}">${group.options.map((option, optionIndex) => `<option value="${optionIndex}">${escapeHtml(option.name)}${option.price ? ` (+${formatPrice(option.price)})` : ''}</option>`).join('')}</select></label>
		`).join('');
		const extras = (item.extras || []).map((extra, extraIndex) => `
			<label class="menu-extra"><input type="checkbox" data-extra-index="${extraIndex}"><span>${escapeHtml(extra.name)}</span><strong>+${formatPrice(extra.price)}</strong></label>
		`).join('');
		const available = !item.productId || partnerMenu[item.productId] !== false;
		return `
		<article class="menu-item ${available ? '' : 'is-unavailable'}">
			<div class="menu-item-main"><h3>${escapeHtml(item.name)}</h3><p>${escapeHtml(item.description)}</p><div class="menu-customizer">${choiceFields}${extras}<label class="menu-option menu-note">Note pour la cuisine<textarea data-item-note maxlength="100" rows="1" placeholder="Ex. sans oignons"></textarea></label></div></div>
			<div class="menu-item-side"><span>${formatPrice(item.price)}</span><button class="add-item" type="button" data-add="${index}" data-restaurant="${restaurantId}" aria-label="Ajouter ${escapeHtml(item.name)}" ${available ? '' : 'disabled'}>${available ? '+' : '—'}</button></div>
		</article>
		`;
	}).join('');
	showDialog(menuDialog);
}

function renderCart() {
	const restaurant = restaurants[cart.restaurantId];
	const itemsElement = document.querySelector('#cart-items');
	const count = cart.items.reduce((total, item) => total + item.quantity, 0);
	const subtotal = cart.items.reduce((total, item) => total + item.price * item.quantity, 0);
	const smallBasketFee = count && subtotal < 10 ? 1 : 0;
	const unavailable = cart.restaurantId === 'dar' && (!isPartnerStoreOpen() || cart.items.some((item) => item.productId && readPartnerMenu()[item.productId] === false));
	document.querySelector('#cart-count').textContent = count;
	document.querySelector('#mobile-cart-count').textContent = count;
	document.querySelector('#cart-restaurant').textContent = restaurant ? restaurant.name : '';
	document.querySelector('#cart-subtotal').textContent = formatPrice(subtotal);
	document.querySelector('#small-basket-row').hidden = smallBasketFee === 0;
	document.querySelector('#cart-total').textContent = formatPrice(subtotal + (count ? 3 : 0) + smallBasketFee);
	document.querySelector('#checkout-button').disabled = count === 0 || unavailable;
	const unavailableMessage = document.querySelector('#cart-unavailable');
	if (unavailableMessage) {
		unavailableMessage.textContent = isPartnerStoreOpen() ? 'Un article de votre panier n’est plus disponible. Modifiez le panier pour continuer.' : 'Le restaurant est temporairement en pause.';
		unavailableMessage.hidden = !unavailable;
	}
	itemsElement.innerHTML = count ? cart.items.map((item) => `
		<div class="cart-line">
			<span>${escapeHtml(item.name)}${item.note ? `<small>${escapeHtml(item.note)}</small>` : ''}</span>
			<div class="quantity-control"><button type="button" data-quantity="-1" data-item="${escapeHtml(item.key)}" aria-label="Retirer un ${escapeHtml(item.name)}">−</button><span>${item.quantity}</span><button type="button" data-quantity="1" data-item="${escapeHtml(item.key)}" aria-label="Ajouter un ${escapeHtml(item.name)}">+</button></div>
			<strong>${formatPrice(item.price * item.quantity)}</strong>
		</div>
	`).join('') : '<p class="cart-empty">Votre panier attend sa première bonne idée.</p>';
}

function addToCart(restaurantId, itemIndex, configuration = {}) {
	if (cart.restaurantId && cart.restaurantId !== restaurantId) {
		cart.restaurantId = restaurantId;
		cart.items = [];
	}
	cart.restaurantId = restaurantId;
	const menuItem = restaurants[restaurantId].items[itemIndex];
	if (restaurantId === 'dar' && (!isPartnerStoreOpen() || (menuItem.productId && readPartnerMenu()[menuItem.productId] === false))) return;
	const choices = configuration.choices || [];
	const extras = configuration.extras || [];
	const note = configuration.note || '';
	const choiceNames = choices.map((choice) => choice.name);
	const item = {
		name: [menuItem.name, ...choiceNames, ...extras.map((extra) => extra.name)].join(' · '),
		price: menuItem.price + choices.reduce((total, choice) => total + choice.price, 0) + extras.reduce((total, extra) => total + extra.price, 0),
		note,
		productId: menuItem.productId || null,
	};
	item.key = `${restaurantId}:${itemIndex}:${choiceNames.join('|')}:${extras.map((extra) => extra.name).join('|')}:${item.note}`;
	const existing = cart.items.find((line) => line.key === item.key);
	if (existing) existing.quantity += 1;
	else cart.items.push({ ...item, quantity: 1 });
	saveCart();
	renderCart();
}


if (menuDialog && cartDialog && backdrop) {
applyCustomerLanguage();
renderCustomerHeader();
renderCart();
document.querySelectorAll('[data-menu]').forEach((button) => {
	button.addEventListener('click', () => openMenu(button.dataset.menu));
});

document.querySelector('#menu-items').addEventListener('click', (event) => {
	const button = event.target.closest('[data-add]');
	if (!button) return;
	const restaurantId = button.dataset.restaurant;
	const itemIndex = Number(button.dataset.add);
	const item = restaurants[restaurantId].items[itemIndex];
	const row = button.closest('.menu-item');
	const choices = (item.choices || []).map((group, groupIndex) => {
		const select = row.querySelector(`[data-choice-index="${groupIndex}"]`);
		const option = group.options[Number(select?.value || 0)];
		return { name: option.name, price: option.price };
	});
	const extras = (item.extras || []).filter((extra, extraIndex) => row.querySelector(`[data-extra-index="${extraIndex}"]`)?.checked);
	const note = row.querySelector('[data-item-note]')?.value.trim() || '';
	addToCart(restaurantId, itemIndex, { choices, extras, note });
	button.textContent = '✓';
	window.setTimeout(() => { if (button.isConnected) button.textContent = '+'; }, 700);
});

document.querySelectorAll('[data-close]').forEach((button) => {
	button.addEventListener('click', closeDialogs);
});
backdrop.addEventListener('click', closeDialogs);
document.querySelector('#open-cart').addEventListener('click', () => { renderCart(); showDialog(cartDialog); });
document.querySelector('#mobile-cart').addEventListener('click', () => { renderCart(); showDialog(cartDialog); });
document.querySelector('#mobile-search').addEventListener('click', () => {
	const searchForm = document.querySelector('.search-form');
	searchForm.classList.add('is-mobile-open');
	document.querySelector('#restaurant-search').focus();
});
document.querySelector('#close-mobile-search').addEventListener('click', () => {
	document.querySelector('.search-form').classList.remove('is-mobile-open');
	document.querySelector('#restaurant-search').blur();
});
document.querySelector('#mobile-history').addEventListener('click', openOrderHistory);
document.querySelector('#open-history').addEventListener('click', openOrderHistory);
document.querySelector('#close-history').addEventListener('click', () => { document.querySelector('#customer-orders').hidden = true; });
document.querySelector('#open-address').addEventListener('click', () => openAddressDialog());
document.querySelector('#change-address').addEventListener('click', () => openAddressDialog());
document.querySelector('#change-checkout-address').addEventListener('click', () => openAddressDialog({ resumeCheckout: true }));
document.querySelector('#open-account').addEventListener('click', openAccountDialog);

document.querySelector('#cart-items').addEventListener('click', (event) => {
	const button = event.target.closest('[data-quantity]');
	if (!button) return;
	const item = cart.items.find((line) => line.key === button.dataset.item);
	if (!item) return;
	item.quantity += Number(button.dataset.quantity);
	cart.items = cart.items.filter((line) => line.quantity > 0);
	if (!cart.items.length) cart.restaurantId = null;
	saveCart();
	renderCart();
});

document.querySelector('#checkout-button').addEventListener('click', () => {
	if (!cart.items.length) return;
	cartDialog.close();
	continueCheckout();
});

document.querySelector('#checkout-form').addEventListener('submit', (event) => {
	event.preventDefault();
	if (!cart.items.length || !cart.restaurantId) return;
	const address = getDeliveryAddress();
	if (!address || !address.covered) {
		openAddressDialog({ resumeCheckout: true });
		return;
	}
	if (!getCustomerProfile()) {
		resumeCheckoutAfterLogin = true;
		checkoutDialog.close();
		openAccountDialog();
		return;
	}
	const formData = new FormData(event.currentTarget);
	const customerName = String(formData.get('customer_name')).trim();
	const profile = getCustomerProfile();
	localStorage.setItem(profileStorageKey, JSON.stringify({ ...profile, name: customerName }));
	const orders = readOrders();
	const order = {
		id: `FD-${Date.now().toString().slice(-6)}`,
		deliveryCode: String(crypto.getRandomValues(new Uint32Array(1))[0] % 1000000).padStart(6, '0'),
		restaurantId: cart.restaurantId,
		restaurant: restaurants[cart.restaurantId].name,
		customer: customerName,
		phone: String(formData.get('phone')).trim(),
		address: String(formData.get('address')).trim(),
		landmark: address.landmark || '',
		coordinates: address.coordinates,
		items: cart.items.map(({ name, price, quantity, note }) => ({ name, price, quantity, note })),
		total: cart.items.reduce((total, item) => total + item.price * item.quantity, 0) + 3 + (cart.items.reduce((total, item) => total + item.price * item.quantity, 0) < 10 ? 1 : 0),
		status: 'placed',
		courier: null,
		createdAt: new Date().toISOString(),
	};
	orders.unshift(order);
	writeOrders(orders);
	cart.items = [];
	cart.restaurantId = null;
	saveCart();
	renderCart();
	event.currentTarget.reset();
	closeDialogs();
	renderCustomerHeader();
	renderCustomerOrders();
	document.querySelector('#order-tracking').scrollIntoView({ behavior: 'smooth', block: 'center' });
});

function renderCustomerOrders() {
	const order = readOrders()[0];
	const tracking = document.querySelector('#order-tracking');
	if (!tracking || !order) return;
	const statusLabels = currentLanguage() === 'ar' ? {
		placed: ['تم تأكيد الطلب', 'أُرسل إلى المطعم', 'state-new'],
		accepted: ['قبل المطعم طلبك', 'قيد التحضير', 'state-preparing'],
		ready: ['طلبك جاهز', 'جاهز للاستلام', 'state-ready'],
		offered: ['تم إرسال عرض إلى الموصّل', 'بانتظار قبول الموصّل', 'state-new'],
		assigned: ['الموصّل في الطريق', 'تم تعيين موصّل', 'state-preparing'],
		picked_up: ['طلبك في الطريق إليك', 'قيد التوصيل', 'state-preparing'],
		delivered: ['بالهناء والشفاء!', 'تم التوصيل', 'state-ready'],
		rejected: ['تم إلغاء الطلب', 'المطعم غير متاح', 'state-late'],
	} : {
		placed: ['Commande confirmée', 'Envoyée au restaurant', 'state-new'],
		accepted: ['Le restaurant a accepté', 'En préparation', 'state-preparing'],
		ready: ['Votre commande est prête', 'Prête à récupérer', 'state-ready'],
		offered: ['Un livreur a reçu la course', 'Offre envoyée', 'state-new'],
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
	if ([partnerMenuStorageKey, partnerStoreStorageKey].includes(event.key)) {
		renderCart();
		if (menuDialog.open && menuDialog.dataset.restaurantId) openMenu(menuDialog.dataset.restaurantId);
	}
});

document.querySelectorAll('.category-chip').forEach((button) => {
	button.addEventListener('click', () => {
		document.querySelectorAll('.category-chip').forEach((chip) => chip.classList.remove('is-active'));
		button.classList.add('is-active');
		filterRestaurants();
	});
});

const sortModes = [
	{ key: 'sortPopular', selector: 'rating', direction: 'desc' },
	{ key: 'sortFast', selector: 'eta', direction: 'asc' },
	{ key: 'sortNearby', selector: 'distance', direction: 'asc' },
];
let sortModeIndex = 0;
document.querySelector('#sort-restaurants').addEventListener('click', () => {
	sortModeIndex = (sortModeIndex + 1) % sortModes.length;
	const mode = sortModes[sortModeIndex];
	const cards = [...document.querySelectorAll('.restaurant-card')];
	cards.sort((left, right) => {
		const difference = Number(left.dataset[mode.selector]) - Number(right.dataset[mode.selector]);
		return mode.direction === 'asc' ? difference : -difference;
	});
	const grid = document.querySelector('#restaurant-grid');
	cards.forEach((card) => grid.append(card));
	document.querySelector('#sort-restaurants [data-i18n]').textContent = translate(mode.key);
});

document.querySelector('#restaurant-search').addEventListener('input', filterRestaurants);

function filterRestaurants() {
	const category = document.querySelector('.category-chip.is-active').dataset.category;
	const normalize = (value) => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('fr');
	const query = normalize(document.querySelector('#restaurant-search').value.trim());
	let visible = 0;
	document.querySelectorAll('.restaurant-card').forEach((card) => {
		const matchesCategory = category === 'all' || card.dataset.category.split(' ').includes(category);
		const menuId = card.querySelector('[data-menu]')?.dataset.menu;
		const menuText = (restaurants[menuId]?.items || []).map((item) => `${item.name} ${item.description}`).join(' ');
		const searchableText = normalize(`${card.dataset.name} ${card.dataset.category} ${menuText}`);
		const matchesQuery = searchableText.includes(query);
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
	if (event.key === 'Escape') {
		backdrop.classList.remove('is-visible');
		document.querySelector('.search-form').classList.remove('is-mobile-open');
	}
});
}
