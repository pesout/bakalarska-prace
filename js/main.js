// GENERAL FUNCTIONS

const _ = id => document.getElementById(id);

const _set = (name, value) => window[name] = value;

const _get = name => window[name];

const _del = name => delete window[name];

const getClass = name => document.getElementsByClassName(name);

const creElem = name => document.createElement(name);

const idListByTag = (tag, parent_id) => Array.prototype.slice.call(_(parent_id).getElementsByTagName(tag)).map(id => id.getAttribute('id'));

const disable = id => _(id).disabled = true;

const enable = id => _(id).disabled = false;

const hide = id => typeof id === "string" ? _(id).style.display = "none" : id.style.display = "none" ;

const show = (id, style = "block") => typeof id === "string" ? _(id).style.display = style : id.style.display = style;

const remove = id => _(id).remove();

const isValid = id => _(id).classList.remove("is-invalid");

const isNotValid = id => _(id).classList.add("is-invalid");

const blurValidation = id => _(id).onblur = () => _(id).checkValidity() ? isValid(id) : isNotValid(id);

const formToObject = form => Object.fromEntries(new FormData(form));

const inRange = (value, min, max) => value >= min && value <= max;

const capFirst = str => str.charAt(0).toUpperCase() + str.slice(1);

const queryUrl = (table, action) => `./app/query.php?action=${action}&table=${table}`;

const queueUrl = action => `./app/queue.php?action=${action}`;

const autocompUrl = (table, search = false) => `./app/query.php?action=autocomplete&table=${table}${search ? `&search=${search}` : ""}`;

const historyUrl = (table, id) => `./app/history.php?table=${table}&id=${id}`;

const logregUrl = (action) => `./app/logreg.php?action=${action}`;

const isPg = (page) => _get("page") === page;

const postData = (form = "") => ({
	mode: "cors",
	method: "POST",
	body: form ? new FormData(form) : null
});

const dialog = (type, message) => {
	switch(type) {
		case "error":
			swal(message, {
				icon: "error",
				buttons: {
					cancel: {
						text: "Zavřít",
						visible: true,
						className: "swal-button-grey",
						closeModal: true
					}
				}
			});
			break;
		case "success":
			swal(message, {
				buttons: false,
				timer: 1400,
				icon: "success"
			});
			break;
		default:
			swal(message, {
				buttons: false,
				timer: 2000,
				icon: "warning"
			});
			break;
	}
};

const loading = (start = true) => {
	if (start) {
		_set("loading", setTimeout(() => show("loading_icon", "inline-block"), 200));		
	}
	else {
		clearTimeout(_get("loading"));
		hide("loading_icon");	
	}
};

// E-MAIL VALIDATION

const validateMail = mail => {
    const r = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return r.test(String(mail).toLowerCase());
};

const checkMailRecipients = recipients => {
	let error_list = [];
	recipients.map(r => {
		if (r.organizace) {
			if (!r.prac_email) error_list = [
				...error_list,
				`${r.jmeno} ${r.prijmeni} je vybrán(a) jako zástupce organizace ${r.organizace_jmeno}, ale nemá zadaný pracovní e-mail`
			]
			if (!validateMail(r.prac_email ?? "a@a.aa")) [
				...error_list,
				`${r.jmeno} ${r.prijmeni} má zadaný pracovní e-mail ve špatném formátu`
			]
		} else {
			if (!r.email) error_list = [
				...error_list,
				`${r.jmeno} ${r.prijmeni} nemá zadaný e-mail`
			]
			if (!r.kontaktovat) error_list = [
				...error_list,
				`${r.jmeno} ${r.prijmeni} má zakázáno kontaktování`
			]
			if (!validateMail(r.email ?? "a@a.aa")) [
				...error_list,
				`${r.jmeno} ${r.prijmeni} má zadaný e-mail ve špatném formátu`
			]
		}
	});
	return error_list;
};

// PANEL FUNCTIONS

const openPanel = panel => {
	if ((_get("opened_panels") ?? []).filter(p => p === panel).length) return;
	_(panel).style.width = "50rem";
	show("overlay");	
	setTimeout(() => _("overlay").style.opacity = "0.4", 10);
	document.onkeyup = e => e.key == "Escape" && closePanel(panel);
	_set("opened_panels", [...(_get("opened_panels") ?? []), panel]);

	if (panel === "sidepanel_create" && isPg("Akce")) {
		_("ucastnici_create").value = parseParticipants([]);
		_set("participants_to_edit", []);
		_set("saved_participants", []);
	}
	if (panel === "sidepanel_search" && isPg("Akce")) {
		_set("participants_to_edit", []);
		_set("saved_participants", []);
	}
};

const closePanel = panel => {
	_(panel).style.width = "0";
	if ((_get("opened_panels") ?? []).length === 1) {
		_("overlay").style.opacity = "0";
		setTimeout(() => hide("overlay"), 700);
	}
	_set("opened_panels", (_get("opened_panels") ?? []).filter(p => p != panel));
	(_get("opened_panels") ?? []).map(p => document.onkeyup = e => e.key == "Escape" && closePanel(p));

	if (panel === "sidepanel_participants") {
		_set("participants_to_edit", _get("saved_participants"));
	} else {
		_del("saved_participants");
	}
	if (panel === "sidepanel_search") (_get("present_columns") ?? []).map(c => removeSearchParam(c));
};

// SEARCH DOM FUNCTIONS

const addSearchParam = () => {
	const bools_array = ["kontaktovat"];
	const autocomp_array = ["organizace", "darce", "typ"];
	const participants_array = ["ucastnici"];

	const box = _("search_form_inputs");
	if (box.lastChild.nodeName === "#text") box.removeChild(box.lastChild);
	
	if (box.lastChild.style.display == "none") {
		box.lastChild.style.display = "block";
		return;
	}
	const old_id = box.lastChild.id;
	const new_id = old_id.replace(/\d+$/, i => ++i);

	const new_inputs = box.lastChild.cloneNode(true);
	new_inputs.id = new_id;
	new_inputs.innerHTML = new_inputs.innerHTML.replace(new RegExp(old_id,"g"), new_id);

	_set("present_columns", [...new Set([...(_get("present_columns") ?? []), new_id])]);

	box.appendChild(new_inputs);

	const name = `${new_id}_name`;
	const input = `${new_id}_content`;
	const exact = `${new_id}_exact`;
	const input_bool = `${new_id}_boolean`;
	show(input, ""); show(exact, ""); hide(input_bool);

	_(name).onchange = () => {
		if (_(input).value === "_%") _(input).value = null;
		const selected = _(name).value;
		show(input, ""); show(exact, ""); hide(input_bool);
		_(`${new_id}_boolean`).value = "";

		if(autocomp_array.includes(selected)) {
			addAutocomplete(input, selected === "typ" ? "AkceTyp" : capFirst(selected), true);
			_set("autocomp-search-field", input);
			_(exact).options[0].disabled = true;
			_(exact).options[1].selected = true;
		} else {
			_(exact).options[0].disabled = false;
			_(exact).options[0].selected = true;
			removeAutocomplete(input);
			_del("autocomp-search-field");
		}
		if (bools_array.includes(selected)) {
			_(input).value = "";
			hide(input); hide(exact); show(input_bool, "");
		}
		if (participants_array.includes(selected)) {
			_(input).autocomplete = "off";
			_(input).value = parseParticipants(_get("saved_participants"));
			_(input).onfocus = () => editParticipants("search");
			_set("search_field_id", input);
			_(exact).options[1].disabled = true;
			_(exact).options[2].disabled = true;
		} else {
			if (_(input).value === parseParticipants(_get("saved_participants"))) _(input).value = "";
			_(input).autocomplete = "on";
			_(input).onfocus = () => null;
			_del("search_field_id");
			_(exact).options[1].disabled = false;
			_(exact).options[2].disabled = false;
		}
	}; 

	_(exact).onchange = () => {
		show(input, "");
		if (_(exact).value === "filled") {
			_(input).value = "_%";
			hide(input);
		}
		else if (_(input).value === "_%") _(input).value = null;
	};
};

const removeSearchParam = id => {
	const box = _("search_form_inputs");
	if (box.lastChild.nodeName === "#text") box.removeChild(box.lastChild);
	if (box.lastChild.previousSibling.nodeName === "#text") {
		box.lastChild.style.display = "none";
	} else {
		_(id).remove();
	};
	_set("present_columns", (_get("present_columns") ?? []).filter(c => c !== id));
};

// GENERATING OF RESULT TABLE

const creTableCell = content => {
	const td = creElem("TD");
	td.innerText = content;
	return td;
};

const creTableCellLinks = (id, typ = 0) => {
	const td = creElem("TD");

	if (isPg("Akce") && typ == 1) {
		const b_mail = creElem("BUTTON");
		b_mail.className = "btn btn-outline-dark btn-sm table-button";
		b_mail.id = `mail_row${id}`;
		b_mail.innerHTML = "<i class='far fa-envelope'></i> Poslat";
		td.appendChild(b_mail);
	}

	if (isPg("Lide") || isPg("Dary") || isPg("Akce")) {
		const b_queue = creElem("BUTTON");
		b_queue.className = "btn btn-outline-dark btn-sm table-button";
		b_queue.id = `queue_row${id}`;
		if (isPg("Lide")) b_queue.innerHTML = "<i class='fas fa-arrow-up'></i> Fronta";
		if (isPg("Dary")) b_queue.innerHTML = "<i class='fas fa-arrow-up'></i> Dárce do fronty";
		if (isPg("Akce")) b_queue.innerHTML = "<i class='fas fa-arrow-up'></i> Účastníci do fronty";
		td.appendChild(b_queue);
	}
	
	const b_edit = creElem("BUTTON");
	b_edit.className = "btn btn-outline-dark btn-sm table-button";
	b_edit.id = `edit_row${id}`;
	b_edit.innerHTML = "<i class='fas fa-info'></i> Detail";
	td.appendChild(b_edit);

	if (!isPg("AkceTyp")) {
		const b_remove = creElem("BUTTON");
		b_remove.className = "btn btn-outline-danger btn-sm table-button";
		b_remove.id = `remove_row${id}`;
		b_remove.innerHTML = "<i class='fas fa-trash-alt'></i> Odstranit";
		td.appendChild(b_remove);
	}

	return td;
};

const parseTdValues = (row, column) => {
	const value = row[column];
	const value_int = parseInt(value);
	if ((value_int === 0 || value_int === 1) && column !== "typ") {
		return value_int ? "ano" : "ne";
	}
	if (row[column] && column === "organizace") return row.organizace_jmeno;
	if (row[column] && column === "darce") return row.darce_jmeno;
	if (row[column] && column === "typ") return row.typ_jmeno;
	const date_test = value?.toString().replace(/[0-9]/g, '') ;
	if (date_test && date_test.startsWith("--")) {
		date = new Date(value);
		return `${date.toLocaleDateString("cs-CZ")} ${date_test === "-- ::" ? `(${date.toLocaleTimeString("cs-CZ", {timeStyle: "short"})})` : ""}`;
	}
	try {
		return JSON.parse(value).map(p => `${p.jmeno} ${p.prijmeni}` + (p.organizace_jmeno ? ` (${p.organizace_jmeno})` : "")).join(", ");
	} catch (e) {
		return value;
	}	
} 

const fillSearchTable = (data = _get("last_query")) => {  
	if (data.length == 0) {
		dialog("warning", "Nenalezen žádný záznam");
		hide("table_result");
		return;
	} else {
		show("table_result", "table");
	}
	let tr;
	_("table-result-body").innerText = null;
	const skip = ["id", "organizace_jmeno", "darce_jmeno", "typ_jmeno"];
	data.map(row => {
		tr = creElem("TR");
		tr.id = `row${row.id}`;
		Object.keys(row).map(column => {
			if (!skip.includes(column)) {
				hide(`${column}_header`);
				if (_(`${column}_display`)?.checked) {
					show(`${column}_header`, "");
					tr.appendChild(creTableCell(
						parseTdValues(row, column)
					));
				}
			}
		});
		tr.appendChild(creTableCellLinks(row.id, row.typ));
		_("table-result-body").appendChild(tr);
		
		_(`row${row.id}`).ondblclick = () => prepareEdit(`row${row.id}`);
		_(`edit_row${row.id}`).onclick = () => prepareEdit(`row${row.id}`);
		if (!isPg("AkceTyp")) _(`remove_row${row.id}`).onclick = () => removeQuery(`row${row.id}`);		

		if (isPg("Lide")) _(`queue_row${row.id}`).onclick = () => moveToQueue(`row${row.id}`);
		if (isPg("Dary")) _(`queue_row${row.id}`).onclick = () => moveDonorsToQueue([row]);
		if (isPg("Akce")) _(`queue_row${row.id}`).onclick = () => moveParticipantsToQueue([row]);

		if (_(`mail_row${row.id}`)) {
			_(`mail_row${row.id}`).onclick = e => {
				const err = checkMailRecipients(JSON.parse(row.ucastnici));
				if (err.length) {
					dialog("error", `${err.join(".\n\n")}.`);
					loading(false);
					return;
				}
				window.open(`mailto:${JSON.parse(row.ucastnici).map(u => u.organizace ? u.prac_email : u.email).join(";")}?subject=${row.nazev}`, "Mail");
				e.preventDefault();
			}
			_(`mail_row${row.id}`).disabled = row.ucastnici === "[]"
		}
	});
};

// QUEUE FUNCTIONS

const fillQueue = data  => {
	if (data.length == 0) {
		hide("queue");
		return;
	}
	show("queue");
	_("queue_items").innerText = null;
	data = Object.values(data);
	data.map(item => {
		const div = creElem("DIV");
		const a = creElem("A");
		div.innerText = `${item.jmeno} ${item.prijmeni}` + (item.as_org ? ` (${item.organizace_jmeno})` : "");	
		a.classList.add("fas", "fa-times");
		a.id = `que${item.id}`;

		div.appendChild(a);
		_("queue_items").appendChild(div);

		_(`que${item.id}`).onclick = () => removeFromQueue(item.id);
	})
}

const moveDonorsToQueue = (donors = _get("last_query")) => {
	moveToQueue(donors.map(d => ({
		id: d.darce,
		organizace: d.organizace,
		darce_jmeno: d.darce_jmeno,
		organizace_jmeno: d.organizace_jmeno
	})));
};

const moveParticipantsToQueue = (events = _get("last_query")) => {
	if (events[0].ucastnici === "[]") {
		dialog("error", "Tato akce nemá přiřazené žádné účastníky");
		return;
	}
	let to_move = [];
	events.map(e => to_move = [...to_move, ...JSON.parse(e.ucastnici).filter(u => !to_move.map(i => i.id).includes(u.id))]);
	moveToQueue(to_move);
};

const moveToQueue = (data = _get("last_query")) => {
	const queueify = params => {
		const formData = new FormData();
		formData.append("params", JSON.stringify(params));
		loading();
		fetch(
			queueUrl("add"), { ...postData(), body: formData }
		).then(
			response => response.json()
		).then(
			data => {
				fillQueue(data);

				const q_count = Object.keys(_get("queue")).length;
				const d_count = Object.keys(data).length;
				if (q_count === d_count) params.length === 1
					? dialog("warning", "Vybraný člověk již ve frontě je")
					: dialog("warning", "Všichni vybraní lidé již ve frontě jsou");

				_set("queue", data);
				loading(false);	
			}
		).catch (
			error => {
				console.error(error);
				loading(false);
				dialog("error", "Došlo k chybě při odesílání požadavku");
			}
		);
	};

	if (typeof data === "object") queueify(data.map(i => [i.id, i.organizace ? true : false]));
	else {
		const row = _get("last_query").find(i => i.id == data.substring(3));
		row.organizace
			? swal("Chcete tohoto člověka vložit do fronty jako zástupce organizace?", {
				closeOnClickOutside: false,
				closeOnEsc: false,
				buttons: {
					confirm: {
						text: "Ano",
						visible: true,
						className: "swal-button-dark",
						value: true,
						closeModal: true
					},
					cancel: {
						text: "Ne",
						visible: true,
						className: "swal-button-grey",
						value: false,
						closeModal: true
					}
				}
			}).then(res => res ? queueify([[row.id, true]]) : queueify([[row.id, false]]))
			: queueify([[row.id, false]]);
	}
}

const removeFromQueue = id =>{
	const formData = new FormData();
	formData.append("id", id);
	fetch(
		queueUrl("remove"), { ...postData(), body: formData }
	).then(
		response => response.json()
	).then(
		data => {
			_set("queue", data);
			fillQueue(data);
		}
	).catch (
		error => {
			console.error(error);
			dialog("error", "Došlo k chybě při odesílání požadavku");
		}
	);
}

const getQueue = () => {
	fetch(
		queueUrl("get"), postData()
	).then(
		response => response.json()		
	).then(
		data => {
			fillQueue(data);
			_set("queue", data);
		}
	).catch (
		error => {
			console.error(error);
			dialog("error", "Došlo k chybě při odesílání požadavku");
		}
	);
}

const clearQueue = () => {
	fetch(
		queueUrl("clear"), postData()
	).then(
		response => response.json()		
	).then(
		data => {
			if (data.state != "success") throw data;
			fillQueue([]);
			_set("queue", []);
		}
	).catch (
		error => {
			console.error(error);
			dialog("error", "Došlo k chybě při odesílání požadavku");
		}
	);
}

// FORM INITIAL VALUES PARSING

const prepareEdit = id => {
	const row = _get("last_query").find((i, j) => {
		_set("edited_index", j);
		return i.id == id.substring(3)
	});
	Object.keys(row).map(column => {
		if (_(`${column}_edit`)) {
			if (row[column] && column == "organizace") _(`${column}_edit`).value = row["organizace_jmeno"];
			else if (row[column] && column == "typ") _(`${column}_edit`).value = row["typ_jmeno"];
			else if (row[column] && column == "darce") _(`${column}_edit`).value = 
				`${row.darce_jmeno} ${row.organizace_jmeno ? `(${row.organizace_jmeno})` : ""}`;
			else if (row[column] && column == "ucastnici") _(`${column}_edit`).value = parseParticipants(JSON.parse(row["ucastnici"]));
			else _(`${column}_edit`).value = row[column];
		}
		_(`${column}_detail`) && (_(`${column}_detail`).innerText = parseTdValues(row, column));
	});
	_set("last_id", id);
	if (isPg("Akce")) {
		_set("participants_to_edit", JSON.parse(row["ucastnici"]));
		_set("saved_participants", _get("participants_to_edit"));
	}
	if (_("sidepanel_history")) prepareHistory(
		row.prijmeni ? "Lide" : "Organizace",
		id.substring(3),
		row.nazev ? row.nazev : `${row.jmeno} ${row.prijmeni}`
	);
	openPanel("sidepanel_edit");
};

const prepareHistory = (table, id, header) => {
	_("history_top_header").innerText = header;
	fetch(
		historyUrl(table, id), { ...postData(), method: "GET" }
	).then(
		response => response.json()	
	).then(
		data => {
			_("table_history_dary_body").innerText = null;
			_("table_history_akce_body").innerText = null;

			if (data.dary.length) {
				data.dary.map(d => {
					const tr = creElem("TR");
					idListByTag("th", "table_history_dary_header").map(col => {
						const td = creElem("TD");
						td.innerText = col === "datum" ? new Date(d[col]).toLocaleDateString("cs-CZ") : d[col];
						tr.appendChild(td);
					});
					_("table_history_dary_body").appendChild(tr);
				});
			} else {
				const tr = creElem("TR");
				idListByTag("th", "table_history_dary_header").map(() => {
					const td = creElem("TD");
					td.innerText = "---";
					tr.appendChild(td);
				});
				_("table_history_dary_body").appendChild(tr);
			}

			if (data.akce.length) {
				data.akce.map(a => {
					const tr = creElem("TR");
					idListByTag("th", "table_history_akce_header").map(col => {
						const td = creElem("TD");
						td.innerText = col === "datum" ? new Date(a[col]).toLocaleDateString("cs-CZ") : a[col];
						tr.appendChild(td);
					});
					_("table_history_akce_body").appendChild(tr);
				});
			} else {
				const tr = creElem("TR");
				idListByTag("th", "table_history_akce_header").map(() => {
					const td = creElem("TD");
					td.innerText = "---";
					tr.appendChild(td);
				});
				_("table_history_akce_body").appendChild(tr);
			}
		}
	).catch (
		error => {
			console.error(error);
			loading(false);
			dialog("error", "Došlo k chybě při odesílání požadavku");
		}
	);
};

// CALL DB QUERIES API

const selectQuery = form => {
	loading();
	const formData = new FormData(form);
	_get("autocomp-search-field") && formData.get(_get("autocomp-search-field")) !== "_%" && formData.set(_get("autocomp-search-field"), _get("autocomp"));
	_get("saved_participants")
		? formData.set("ucastnici", JSON.stringify(_get("saved_participants").map(i => i.id)))
		: formData.delete("ucastnici");
	fetch(
		queryUrl(_get("page"), "select"), { ...postData(form), body: formData }
	).then(
		response => response.json()	
	).then(
		data => {
			_set("last_query", data);	
			fillSearchTable(data);
			loading(false);
			_del("saved_participants");
			closePanel("sidepanel_search");					
		}
	).catch (
		error => {
			console.error(error);
			loading(false);
			dialog("error", "Došlo k chybě při odesílání požadavku");
		}
	);
};

const createQuery = form => {
	loading();
	const formData = new FormData(form);
	if (formData.get("organizace")) {
		formData.delete("organizace");
		formData.set("organizace", _get("autocomp"));
	}
	if (formData.get("darce")) {
		formData.delete("darce");
		formData.set("darce", _get("autocomp"));
		_get("autocomp_advanced") && formData.set("organizace", _get("autocomp_advanced"));
	}
	if (formData.get("typ")) {
		formData.delete("typ");
		formData.set("typ", _get("autocomp"));
	}
	if (_get("saved_participants")) {
		if (formData.get("typ") == 1) {
			const e = checkMailRecipients(_get("saved_participants"));
			if (e.length) {
				dialog("error", `${e.join(".\n\n")}.`);
				loading(false);
				return;
			}
		}
		formData.set("ucastnici", JSON.stringify(_get("saved_participants").map(i => ({ id: i.id, organizace: i.organizace })))) 
	} else {
		formData.delete("ucastnici");
	}

	fetch(
		queryUrl(_get("page"), "create"), { ...postData(form), body: formData }
	).then(
		response => response.json()
	).then(
		data => {
			_set("last_query", _get("last_query") ? [ ..._get("last_query"), ...data] : [...data])
			fillSearchTable();
			loading(false);
			closePanel("sidepanel_create");
			if (_get("autocomp")) {
				_del("autocomp");
				_del("autocomp_advanced");
				if (_(_get("last_autocomp_field"))) _(_get("last_autocomp_field")).innerText = null;
			}
			_del("saved_participants");
			_("create").reset();
			dialog("success", "Záznam přidán úspěšně");
		}
	).catch (
		error => {
			console.error(error);
			loading(false);
			dialog("error", "Došlo k chybě při odesílání požadavku");
		}
	);
};

const editQuery = form => {
	loading();
    const formData = new FormData(form);
    formData.append("row", _get("last_id"));
	if (formData.get("organizace")) {
		formData.delete("organizace");
		formData.set("organizace", _get("autocomp") ? _get("autocomp") : undefined);
	}
	if (formData.get("darce")) {
		formData.delete("darce");
		formData.set("darce", _get("autocomp"));
		formData.set("organizace", _get("autocomp_advanced"));
	}
	if (formData.get("typ")) {
		formData.delete("typ");
		formData.set("typ", _get("autocomp"));
	}
	if (_get("saved_participants")) {
		if (formData.get("typ") == 1) {
			const e = checkMailRecipients(_get("saved_participants"));
			if (e.length) {
				dialog("error", `${e.join(".\n\n")}.`);
				loading(false);
				return;
			}
		}
		formData.set("ucastnici", JSON.stringify(_get("saved_participants").map(i => ({ id: i.id, organizace: i.organizace })))) 
	} else {
		formData.delete("ucastnici");
	}

	fetch(
		queryUrl(_get("page"), "edit"), { ...postData(form), body: formData }
	).then(
		response => response.json()
	).then(
		data => {
			const query = _get("last_query");
			const index = _get("edited_index");
			query[index] = { ...query[index], ...data, id: +_get("last_id").substring(3) }

			_set("last_query", query);
			fillSearchTable(query);
			loading(false);
			closePanel("sidepanel_edit");
			if (_get("autocomp")) {
				_del("autocomp");
				_del("autocomp_advanced");
				_(_get("last_autocomp_field")).innerText = null;
			}
			_del("saved_participants");
			_("edit").reset();
			dialog("success", "Záznam editován úspěšně");
		}
	).catch (
		error => {
			console.error(error);
			loading(false);
			dialog("error", "Došlo k chybě při odesílání požadavku");
		}
	);
};

const removeQuery = id => {
	swal("Opravdu chcete tento záznam odstranit?", {
		dangerMode: true,
		closeOnClickOutside: false,
		closeOnEsc: false,
		buttons: {
			cancel: {
				text: "Ne",
				visible: true,
				className: "swal-button-grey",
				value: false,
				closeModal: true
			},
			confirm: {
				text: "Ano",
				visible: true,
				className: "swal-button-red",
				value: true,
				closeModal: true
			},
		}
	}).then((res) => {
		if (!res) return;
		loading();
		const formData = new FormData();
		formData.append("row", id);
		fetch(
			queryUrl(_get("page"), "delete"), { ...postData(), body: formData }
		).then(
			response => response.json()
		).then(
			data => {
				if (data.state != "success") throw data;
				let index;
				const query = _get("last_query");
				query.find((i, j) => (index = j) && i.id == id.substring(3));			
				_set("last_query", query);
				if (!query.length) hide("table_result");			

				remove(id);
				loading(false);
				closePanel("sidepanel_edit");

				const queue = Object.values(_get("queue")) ?? [];
				const item = queue.find((i) => i.id == id.substring(3));
				item?.id && removeFromQueue(item.id);
				dialog("success", "Záznam odstraněn úspěšně");
			}
		).catch (
			error => {
				console.error(error);
				loading(false);
				dialog("error", "Došlo k chybě při odesílání požadavku");
			}
		);
	});	
};

// AUTOCOMPLETE FUNCTIONS

const addAutocomplete = (el, table, search = false, participants = false) => {
	const autocomp = `${el}_autocomplete`;
	!participants && _set("last_autocomp_field", autocomp);
	const fillAutocomplete = data => {
		_(autocomp).innerText = null;
		data.filter(i => participants ? !(_get("participants_to_edit") ?? []).map(p => p.id).includes(i.id) : i).map((i, j) => {
			const tr = creElem("TR");
			const td = creElem("TD");
			td.className = "option";
			td.innerText = i.nazev 
				? i.nazev 
				: i.organizace_jmeno && !search
					? `${i.jmeno} ${i.prijmeni} (${i.organizace_jmeno})`
					: `${i.jmeno} ${i.prijmeni}`
			td.id = `${autocomp}_option_${i.id}_${j}`;
			tr.appendChild(td);
			_(autocomp).appendChild(tr);
			_(td.id).onmousedown = function() {
				hide(autocomp);
				_(el).value = this.innerText;
				if (participants) displayParticipants([...(_get("participants_to_edit") ?? []), i], search)
				else {
					_set("autocomp", i.id);
					i.organizace ? _set("autocomp_advanced", i.organizace) : _set("autocomp_advanced", "");
				}
			}
		})
	}

	const handleAutocomp = () => {
		if (_(el).value.length > 2) {
			const formData = new FormData();
			formData.append("value", _(el).value);
			fetch(
				autocompUrl(table, search), { ...postData(), body: formData }
			).then(
				response => response.json()
			).then(
				data => {
					fillAutocomplete(data);
					show(autocomp, "table");
				}
			).catch (
				error => {
					console.error(error);
					dialog("error", "Došlo k chybě při odesílání požadavku");
				}
			);
		} else hide(autocomp);
	}

	_(el).onkeyup = () => {
		if (!participants) {
			_get("autocomp") && _del("autocomp");
			_get("autocomp_advanced") && _del("autocomp_advanced");
		}
		handleAutocomp();
	};
	_(el).onclick = handleAutocomp;
	_(el).onblur = () => {
		if (!_get("autocomp")) _(el).value = "";
		hide(autocomp);
		if (participants) {
			hide(el);
			_(el).value = "";
		}
	};
};

const removeAutocomplete = el => {
	const autocomp = `${el}_autocomplete`;
	_(autocomp) && hide(autocomp);
	_(el).onblur = null;
	_(el).onclick = null;
	_(el).onkeyup = null;
	_del("autocomp");
	_del("autocomp_advanced");
	_del("last_autocomp_field");
};

// MULTIPLE VALUES AUTOCOMPLETE FUNCTIONS (PANEL)

displayParticipantsFromQueue = () => {
	const queue = Object.values(_get("queue") ?? [])
		.map(i => i.as_org ? i : { ...i, organizace: null, organizace_jmeno: null })
		.filter(i => !(_get("participants_to_edit") ?? []).map(i => i.id).includes(i.id));
	displayParticipants([...(_get("participants_to_edit") ?? []), ...queue]);
	hide("insert_from_queue");
};

const displayParticipants = (participants = _get("participants_to_edit"), search = false) => {
	_set("participants_to_edit", participants);
	(participants ?? []).length ? show("remove_all_participants", "") : hide("remove_all_participants");

	_("participants").innerText = null;
	(participants ?? []).map(item => {
		const div = creElem("DIV");
		const a = creElem("A");
		div.innerText = `${item.jmeno} ${item.prijmeni}` + (!search && item.organizace_jmeno ? ` (${item.organizace_jmeno})` : "");	
		a.classList.add("fas", "fa-times");
		a.id = `part${item.id}`;

		div.appendChild(a);
		_("participants").appendChild(div);

		_(`part${item.id}`).onclick = () => displayParticipants(_get("participants_to_edit").filter(i => i.id != item.id), search);
	})
};

const editParticipants = (sidepanel_name) => {
	_get("queue").length ? show("insert_from_queue", "") : hide("insert_from_queue");
	addAutocomplete("add_participant", "_advanced", sidepanel_name === "search", true);
	displayParticipants(undefined, sidepanel_name === "search");
	openPanel("sidepanel_participants");
};

const saveParticipants = (field = _get("search_field_id")) => {
	_set("saved_participants", _get("participants_to_edit"));
	if (field && _(field)) _(field).value = parseParticipants(_get("saved_participants"));
	_("ucastnici_edit").value = parseParticipants(_get("saved_participants"));
	_("ucastnici_create").value = parseParticipants(_get("saved_participants"));
	closePanel("sidepanel_participants");
};

parseParticipants = (participants) => {
	if (!participants) return;
	if (participants.length === 0) {
		return "Vyberte...";
	} else if (participants.length <= 3) {
		return `${participants.map(p => p.prijmeni).join(", ")}`;
	} else {
		return `${participants.map(p => p.prijmeni).slice(0, 2).join(", ")} + ${participants.length - 2}`;
	}
};

const logout = () => {
	fetch(
		logregUrl("logout"), postData()
	).then(
		response => response.json()
	).then(
		() => window.location.replace("?page=lide")
	).catch (
		error => {
			console.error(error);
			dialog("error", "Došlo k chybě při odesílání požadavku");
		}
	);
};

// AFTER LOAD

document.addEventListener('DOMContentLoaded', () => {

	// Get page name
	const page = capFirst(new URLSearchParams(window.location.search).get('page'));
	_set("page", page === "Typy-akci" ? "AkceTyp" : page);

	if (!isPg("Prihlaseni") && !isPg("Registrace")) {

		// Queue
		getQueue();

		// Initial select
		selectQuery(_("search"));

		// Buttons handling
		_("new_search").onclick = (e) => {
			e.preventDefault();
			const hash = Object.create(null),
				duplication = [].some.call(getClass('column-name'), i => {
					if (i.value) {
						if(hash[i.value]) return true;
						hash[i.value] = true;
					}
				});
			if (duplication) {
				dialog("error", "Parametry se nesmí opakovat");
				return;
			}
			selectQuery(_("search"));
		}

		_("new_create").onclick = (e) => {
			e.preventDefault();
			let form_ok = true;
			if (_("telefon_create")) _("telefon_create").value = _("telefon_create").value.replace(/\ /g, "");
			idListByTag("input", "create").map(id => {
				if (_(id).checkValidity()) isValid(id);
				else {
					isNotValid(id);
					form_ok = false;
				}					
			});
			if (form_ok) createQuery(_("create"));
		}

		_("new_edit").onclick = (e) => {
			e.preventDefault();
			let form_ok = true;
			if (_("telefon_edit")) _("telefon_edit").value = _("telefon_edit").value.replace(/\ /g, "");
			idListByTag("input", "edit").map(id => {
				if (_(id).checkValidity()) isValid(id);
				else {
					isNotValid(id);
					form_ok = false;
				}							
			});
			if (form_ok) editQuery(_("edit"));
		}
		
		if (!isPg("AkceTyp")) _("new_remove").onclick = (e) => {
			e.preventDefault();
			removeQuery(_get("last_id"));
		}

		// Form validation initialize
		idListByTag("input", "create").map(id => blurValidation(id));
		idListByTag("input", "edit").map(id => blurValidation(id));


		// Add autocomplete to inputs
		_("organizace_edit") && addAutocomplete("organizace_edit", "Organizace");
		_("organizace_edit") && addAutocomplete("organizace_create", "Organizace");

		_("typ_edit") && addAutocomplete("typ_edit", "AkceTyp");
		_("typ_edit") && addAutocomplete("typ_create", "AkceTyp");

		_("darce_edit") && addAutocomplete("darce_edit", "_advanced");
		_("darce_edit") && addAutocomplete("darce_create", "_advanced");
		
	} else {

		_("logreg_button").onclick = (e) => {
			e.preventDefault();
			if (isPg("Prihlaseni")) {
				if(!_("username").value) { dialog("error", "Uživatelské jméno musí být vyplněno"); return; }
				if(!_("password").value) { dialog("error", "Heslo musí být vyplněno"); return; }
			}
			if (isPg("Registrace")) {
				if(!_("username").value) { dialog("error", "Uivatelské jméno musí být vyplněno"); return; }
				if(!_("password").value) { dialog("error", "Heslo musí být vyplněno"); return; }
				if(!_("password2").value) { dialog("error", "Zadejte prosím heslo pro kontrolu"); return; }
				if(!_("code").value) { dialog("error", "Zvací kód musí být vyplněn"); return; }
				if(_("password").value !== _("password2").value) { dialog("error", "Hesla nejsou stejná"); return; }
			}

			fetch(
				logregUrl(isPg("Prihlaseni") ? "login" : "registration"), postData(_("logreg"))
			).then(
				response => response.json()
			).then(
				data => {
					if (data.status === "ok") {
						window.location.replace("?page=lide");
					} else {
						switch (data.status) {
							case "unallowed_username_chars":
								dialog("error", "Uživatelské jméno obsahuje nepovolené znaky");
								break;
							case "user_not_exist":
								dialog("error", "Uživatel neexistuje");
								break;
							case "incorrect_password":
								dialog("error", "Nesprávně zadané heslo");
								break;
							case "incorrect_code":
								dialog("error", "Nesprávný zvací kód");
								break;
							case "short_username":
								dialog("error", "Uživatelské jméno je příliš krátké");
								break;
							case "short_password":
								dialog("error", "Heslo je příliš krátké");
								break;
							case "user_exists":
								dialog("error", "Uživatel s tímto jménem již existuje");
								break;
						}
					}
				}
			).catch (
				error => {
					console.error(error);
					dialog("error", "Došlo k chybě při odesílání požadavku");
				}
			);
		}
	}

});
