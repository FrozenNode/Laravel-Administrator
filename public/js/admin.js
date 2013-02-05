(function($)
{
	var admin = function()
	{
		return this.init();
	};

	admin.prototype = {

		//properties

		/*
		 * Main admin container
		 *
		 * @type jQuery object
		 */
		$container: null,

		/*
		 * If this is true, history.js has started
		 *
		 * @type bool
		 */
		historyStarted: false,


		/*
		 * Filters view model
		 */
		filtersViewModel: {

			/* The filters for the current result set
			 * array
			 */
			filters: [],

			/* The options lists for any fields
			 * object
			 */
			listOptions: {}
		},

		/*
		 * KO viewModel
		 */
		viewModel: {

			/*
			 * KO data model
			 */
			model: {},


			/* The model name for this data model
			 * string
			 */
			modelName: ko.observable(''),

			/* The model title for this data model
			 * string
			 */
			modelTitle: ko.observable(''),

			/* The title for single items of this model
			 * string
			 */
			modelSingle: ko.observable(''),

			/* The link (usually front-end) associated with this item
			 * string
			 */
			itemLink: ko.observable(null),

			/* The expand width of the edit area
			 * int
			 */
			expandWidth: ko.observable(null),

			/* The primary key value for this model
			 * string
			 */
			primaryKey: 'id',

			/* The rows of the current result set
			 * array
			 */
			rows: ko.observableArray(),

			/* The number of rows per page
			 * int
			 */
			rowsPerPage: ko.observable(20),

			/* The options (1-100 ...set up in init method) for the rows per page
			 * array
			 */
			rowsPerPageOptions: [],

			/* The columns for the current data model
			 * object
			 */
			columns: [],

			/* The options lists for any fields
			 * object
			 */
			listOptions: {},

			/* The current sort options
			 * object
			 */
			sortOptions: {
				field: ko.observable(),
				direction: ko.observable()
			},

			/* The current pagination options
			 * object
			 */
			pagination: {
				page: ko.observable(),
				last: ko.observable(),
				total: ko.observable(),
				per_page: ko.observable(),
				isFirst: true,
				isLast: false,
			},

			/* The model edit fields
			 * array
			 */
			editFields: ko.observableArray(),

			/* The id of the active item. If it's null, there is no active item. If it's 0, the active item is new
			 * mixed (null, int)
			 */
			activeItem: ko.observable(null),

			/* The id of the last active item. This is set to null when an item is closed. 0 is new.
			 * mixed (null, int)
			 */
			lastItem: null,

			/* If this is set to true, the loading screen will be visible
			 * bool
			 */
			loadingItem: ko.observable(false),

			/* The id of the item currently being loaded
			 * int
			 */
			itemLoadingId: ko.observable(null),

			/* If this is set to true, the row loading screen will be visible
			 * bool
			 */
			loadingRows: ko.observable(false),

			/* The id of the rows currently being loaded
			 * int
			 */
			rowLoadingId: 0,

			/* If this is set to true, the form becomes uneditable
			 * bool
			 */
			freezeForm: ko.observable(false),

			/* If this is set to true, the action buttons on the form cannot be accessed
			 * bool
			 */
			freezeActions: ko.observable(false),

			/* If custom actions are supplied, they are stored here
			 * array
			 */
			actions: [],

			/* Holds the per-action permissions
			 * array
			 */
			actionsPermissions: {},

			/* The languages array holds text for the current language
			 * array
			 */
			languages: ko.observableArray(),

			/* The status message and the type ('', 'success', 'error')
			 * strings
			 */
			statusMessage: ko.observable(''),
			statusMessageType: ko.observable(''),

			/**
			 * Saves the item with the current settings. If id is 0, the server interprets it as a new item
			 */
			saveItem: function()
			{
				var self = this,
					saveData = ko.mapping.toJS(self);

				saveData.csrf_token = csrf;

				//if this is a new item, delete the primary key from the data array
				if (!saveData[self.primaryKey])
					delete saveData[self.primaryKey];

				self.statusMessage(self.languages['saving']).statusMessageType('');
				self.freezeForm(true);

				$.ajax({
					url: base_url +  self.modelName() + '/' + self[self.primaryKey]() + '/save',
					data: saveData,
					dataType: 'json',
					type: 'POST',
					complete: function()
					{
						self.freezeForm(false);
					},
					success: function(response)
					{
						if (response.success) {
							//$('#users_list').trigger('reloadGrid');
							self.statusMessage(self.languages['saved']).statusMessageType('success');
							self[self.primaryKey](response.data[self.primaryKey]);
							self.activeItem(response.data[self.primaryKey]);
							self.updateRows();
							self.updateSelfRelationships();

							setTimeout(function()
							{
								History.pushState({modelName: self.modelName()}, null, route + self.modelName());
							}, 200);
						}
						else
						{
							var error = typeof response.errors == 'string' ? response.errors : response.errors.join(' ');
							self.statusMessage(error).statusMessageType('error');
						}
					}
				});
			},

			/**
			 * Deletes the active item
			 */
			deleteItem: function()
			{
				var self = this,
					conf = confirm(self.languages['delete_active_item']);

				if (!conf)
					return false;

				self.statusMessage(self.languages['deleting']).statusMessageType('');
				self.freezeForm(true);

				$.ajax({
					url: base_url + self.modelName() + '/' + self[self.primaryKey]() + '/delete',
					data: {csrf_token: csrf},
					dataType: 'json',
					type: 'POST',
					success: function(response)
					{
						if (response.success)
						{
							self.statusMessage(self.languages['deleted']).statusMessageType('success');
							self.updateRows();
							self.updateSelfRelationships();

							setTimeout(function()
							{
								History.pushState({modelName: self.modelName()}, null, route + self.modelName());
							}, 500);
						}
						else
							self.statusMessage(response.error).statusMessageType('error');
					}
				});
			},

			/**
			 * Callback for clicking an item
			 */
			clickItem: function(id)
			{
				if (!this.loadingItem() && this.activeItem() !== id)
				{
					History.pushState({modelName: this.modelName(), id: id}, null, route + this.modelName() + '/' + id);
				}
			},

			/**
			 * Gets the active item in the grid
			 *
			 * @param int	id
			 */
			getItem: function(id)
			{
				var self = this;


				//if this is a new item (id is falsy), just overwrite the viewModel with the original data model
				if (!id)
				{
					ko.mapping.updateData(self, self.model, self.model);
					self.itemLoadingId(null);
					self.activeItem(0);

					//set the last item property which helps manage the animation states
					self.lastItem = id;

					return;
				}

				self.loadingItem(true);
				self.itemLoadingId(id);

				$.ajax({
					url: base_url + self.modelName() + '/' + id,
					dataType: 'json',
					success: function(data)
					{
						if (self.itemLoadingId() !== id)
						{
							//if there are no currently-loading items, clear the form
							if (self.itemLoadingId() === null)
							{
								self.loadingItem(false);
								self.clearItem();
							}

							return;
						}

						//set the active item and update the model data
						self.activeItem(data[self.primaryKey]);
						self.loadingItem(false);

						ko.mapping.updateData(self, self.model, data);

						//set the new options for relationships
						$.each(adminData.edit_fields, function(ind, el)
						{
							if (el.relationship && el.autocomplete)
							{
								self.listOptions[el.field](data[el.field + '_options']);
							}
						});

						//set the item link if it exists
						if (data.admin_item_link)
						{
							self.itemLink(data.admin_item_link);
						}

						//set the last item property which helps manage the animation states
						self.lastItem = id;

						//fixes an error where the relationships wouldn't load
						setTimeout(function()
						{
							ko.mapping.updateData(self, self.model, data);
						}, 50);
					}
				});
			},

			/**
			 * Closes the item edit/create window
			 */
			closeItem: function()
			{
				History.pushState({modelName: this.modelName()}, null, route + this.modelName());
			},

			/**
			 * Clears the current item
			 */
			clearItem: function()
			{
				this.freezeForm(false);
				this.statusMessage('');
				this.statusMessageType('');
				this.itemLink(null);
				this.itemLoadingId(null);
				this.activeItem(null);
				this.lastItem = null;
			},

			/**
			 * Opens the create item form
			 */
			addNewItem: function()
			{
				//$('#users_list').resetSelection();
				this.getItem(0);
			},

			/**
			 * Performs a custom action
			 *
			 * @param string	action
			 * @param object	messages
			 */
			customAction: function(action, messages)
			{
				var self = this;

				self.statusMessage(messages.active).statusMessageType('');
				self.freezeForm(true);

				$.ajax({
					url: base_url + self.modelName() + '/' + self[self.primaryKey]() + '/custom_action',
					data: {csrf_token: csrf, action_name: action},
					dataType: 'json',
					type: 'POST',
					complete: function()
					{
						self.freezeForm(false);
					},
					success: function(response)
					{
						if (response.success)
						{
							self.statusMessage(messages.success).statusMessageType('success');
							self.updateRows();
						}
						else
							self.statusMessage(response.error).statusMessageType('error');
					}
				});
			},

			/**
			 * Updates the rows given the data model's current state. Set sort, filters, and anything else before you call this.
			 * Calling this locks the results table.
			 *
			 * @param object	data
			 */
			updateRows: function()
			{
				var self = this,
					id = ++self.rowLoadingId,
					data = {
						csrf_token: csrf,
						sortOptions: self.sortOptions,
						filters: self.getFilters(),
						page: self.pagination.page()
					};

				//if we're on page 0 (i.e. there is currently no result set, set the page to 1)
				if (!data.page)
					data.page = 1;

				//set loadingRows to true so that the loading box comes up
				self.loadingRows(true);

				$.ajax({
					url: base_url + self.modelName() + '/results',
					type: 'POST',
					dataType: 'json',
					data: data,
					success: function(response)
					{
						//if the row loading id has changed, that means it's old...so don't use this data
						if (self.rowLoadingId !== id)
						{
							return;
						}

						//otherwise the rows aren't loading anymore and we can replace the data
						self.pagination.page(response.last ? response.page : response.last);
						self.pagination.last(response.last);
						self.pagination.total(response.total);
						self.rows(response.results);
						self.loadingRows(false);

					}
				});
			},

			/**
			 * Updates the sort options when a column header is clicked
			 *
			 * @param string	field
			 */
			setSortOptions: function(field)
			{
				//check if the field is a valid column
				var found = false;

				//iterate over the columns to check if it's a valid sort_field or field
				$.each(this.columns, function(i, col)
				{
					if (field === col.sort_field || field === col.field)
					{
						found = true;
						return false;
					}
				})

				if (!found)
					return false;

				//the direction depends on the field
				if (field == this.sortOptions.field())
					//reverse the direction
					this.sortOptions.direction( (this.sortOptions.direction() == 'asc') ? 'desc' : 'asc' );
				else
					//set the direction to asc
					this.sortOptions.direction('asc');

				//update the field
				this.sortOptions.field(field);

				//update the rows
				this.updateRows();
			},

			/**
			 * Goes to the specified page
			 *
			 * @param string|int	page
			 */
			page: function(page)
			{
				var currPage = parseInt(this.pagination.page()),
					newPage = 1,
					lastPage = parseInt(this.pagination.last());

				//if the value is 'prev' or 'next', increment or decrement
				if (page === 'prev')
				{
					if (currPage > 1)
					{
						newPage = currPage - 1;
					}
				}
				else if (page === 'next')
				{
					if (currPage < lastPage)
					{
						newPage = currPage + 1;
					}
					else
					{
						newPage = lastPage;
					}
				}
				else if (!isNaN(parseInt(page)))
				{
					//set the page to the supplied value
					if (page > lastPage)
					{
						newPage = lastPage;
					}
					else
					{
						newPage = page;
					}
				}

				this.pagination.page(newPage);

				//update the rows
				this.updateRows();
			},

			/**
			 * Updates the rows per page for this model when the item is changed
			 *
			 * @param int
			 */
			updateRowsPerPage: function(rows)
			{
				var self = this;

				$.ajax({
					url: rows_per_page_url,
					data: {csrf_token: csrf, rows: rows},
					dataType: 'json',
					type: 'POST',
					complete: function()
					{
						self.updateRows();
					}
				});
			},

			/**
			 * Gets a minimized filters array that can be sent to the server
			 */
			getFilters: function()
			{
				var filters = [],
					observables = ['value', 'minValue', 'maxValue'];

				$.each(window.admin.filtersViewModel.filters, function(ind, el)
				{
					var filter = {
						field: el.field,
						type: el.type,
						value: el.value() ? el.value() : null,
					};

					//iterate over the observables to see if we should include them
					$(observables).each(function()
					{
						if (this in el)
						{
							filter[this] = el[this]() ? el[this]() : null;
						}
					});

					//push this filter onto the filters array
					filters.push(filter);
				});

				return filters;
			},

			/**
			 * Updates any self-relationships
			 */
			updateSelfRelationships: function()
			{
				var self = this;

				//first we will iterate over the filters and update them if any exist
				$.each(window.admin.filtersViewModel.filters, function(ind, filter)
				{
					var fieldIndex = ind,
						fieldName = filter.field;

					if ((!filter.constraints || !filter.constraints.length) && filter.selfRelationship)
					{
						window.admin.filtersViewModel.filters[fieldIndex].loadingOptions(true);

						$.ajax({
							url: base_url + self.modelName() + '/update_options/',
							type: 'POST',
							dataType: 'json',
							data: {
								type: 'filter',
								field: fieldName,
								selectedItems: filter.value()
							},
							complete: function()
							{
								window.admin.filtersViewModel.filters[fieldIndex].loadingOptions(false);
							},
							success: function(response)
							{
								//update the options
								window.admin.filtersViewModel.listOptions[fieldName](response);
							}
						});

					}
				});

				//then we'll update the edit fields
				$.each(self.editFields, function(ind, field)
				{
					var fieldIndex = ind,
						fieldName = field.field;

					//if there are constraints to maintain, set up the subscriptions
					if ((!field.constraints || !field.constraints.length) && field.selfRelationship)
					{
						self.editFields[fieldIndex].loadingOptions(true);

						$.ajax({
							url: base_url + self.modelName() + '/update_options/',
							type: 'POST',
							dataType: 'json',
							data: {
								type: 'edit',
								field: fieldName,
								selectedItems: self[fieldName]()
							},
							complete: function()
							{
								self.editFields[fieldIndex].loadingOptions(false);
							},
							success: function(response)
							{
								//update the options
								self.listOptions[fieldName](response);
							}
						});

					}
				});
			}
		},



		//methods

		/**
		 * Init method
		 */
		init: function()
		{
			//set up the basic pieces of data
			this.viewModel.model = adminData.data_model;
			this.$container = $('#admin_content');

			var viewModel = ko.mapping.fromJS(this.viewModel.model);

			$.extend(this.viewModel, viewModel);

			this.viewModel.rows(adminData.rows.results);
			this.viewModel.pagination.page(adminData.rows.page);
			this.viewModel.pagination.last(adminData.rows.last);
			this.viewModel.pagination.total(adminData.rows.total);
			this.viewModel.sortOptions.field(adminData.sortOptions.field);
			this.viewModel.sortOptions.direction(adminData.sortOptions.direction);
			this.viewModel.columns = adminData.column_model;
			this.viewModel.modelName(adminData.model_name);
			this.viewModel.modelTitle(adminData.model_title);
			this.viewModel.modelSingle(adminData.model_single);
			this.viewModel.expandWidth(adminData.expand_width);
			this.viewModel.rowsPerPage(adminData.rows_per_page);
			this.viewModel.primaryKey = adminData.primary_key;
			this.viewModel.actions = adminData.actions;
			this.viewModel.actionPermissions = adminData.action_permissions;
			this.viewModel.languages = adminData.languages;

			//set up the rowsPerPageOptions
			for (var i = 1; i <= 100; i++)
			{
				this.viewModel.rowsPerPageOptions.push(i);
			}

			//now that we have most of our data, we can set up the computed values
			this.initComputed();

			//prepare the filters
			this.filtersViewModel.filters = this.prepareFilters();

			//prepare the edit fields
			this.viewModel.editFields = this.prepareEditFields();

			//set up the relationships
			this.initRelationships();

			//set up the KO bindings
			ko.applyBindings(this.viewModel, $('#main_content')[0]);
			ko.applyBindings(this.filtersViewModel, $('#filters_sidebar_section')[0]);


			//set up pushstate history
			this.initHistory();

			//set up the subscriptions
			this.initSubscriptions();

			//set up the events
			this.initEvents();

			return this;
		},

		/**
		 * Prepare the filters
		 *
		 * @return array with value observables
		 */
		prepareFilters: function()
		{
			var filters = [];

			$.each(adminData.filters, function(ind, filter)
			{
				var observables = ['value', 'minValue', 'maxValue'];

				//iterate over the desired observables and check if they're there. if so, assign them an observable slot
				$.each(observables, function(i, obs)
				{
					if (obs in filter)
					{
						filter[obs] = ko.observable(filter[obs]);
					}
				});

				//if this is a relationship field, we want to set up the loading options observable
				if (filter.relationship)
				{
					filter.loadingOptions = ko.observable(false);
				}

				filter.field_id = 'filter_field_' + filter.field;

				filters.push(filter);
			});

			return filters;
		},

		/**
		 * Prepare the edit fields
		 *
		 * @return object with loadingOptions observables
		 */
		prepareEditFields: function()
		{
			var self = this,
				fields = [];

			$.each(adminData.edit_fields, function(ind, field)
			{
				//if this is a relationship field, set up the loadingOptions observable
				if (field.relationship)
				{
					field.loadingOptions = ko.observable(false);
				}

				//if this is an image field, set the upload params
				if (field.type === 'image')
				{
					field.uploading = ko.observable(false);
					field.upload_percentage = ko.observable(0);
				}

				//add the id field
				field.field_id = 'edit_field_' + ind;

				fields.push(field);
			});

			return fields;
		},

		/**
		 * Set up the relationship items
		 */
		initRelationships: function()
		{
			var self = this;

			//set up the filters
			$.each(adminData.filters, function(ind, el)
			{
				if (el.relationship)
					self.filtersViewModel.listOptions[ind] = ko.observableArray(el.options);
			});

			//set up the edit fields
			$.each(adminData.edit_fields, function(ind, el)
			{
				if (el.relationship)
					self.viewModel.listOptions[ind] = ko.observableArray(el.options);
			});
		},

		/**
		 * Inits the KO subscriptions
		 */
		initSubscriptions: function()
		{
			var self = this,
				runFilter = function(val)
				{
					self.viewModel.updateRows();
				};

			//iterate over filters
			$.each(self.filtersViewModel.filters, function(ind, filter)
			{
				//subscribe to the value field
				self.filtersViewModel.filters[ind].value.subscribe(function(val)
				{
					//if this is an id field, make sure it's an integer
					if (self.filtersViewModel.filters[ind].type === 'key')
					{
						var intVal = isNaN(parseInt(val)) ? '' : parseInt(val);

						self.filtersViewModel.filters[ind].value(intVal);
					}

					//update the rows now that we've got new filters
					self.viewModel.updateRows();
				});



				//check if there's a min and max value. if so, subscribe to those as well
				if ('minValue' in filter)
				{
					self.filtersViewModel.filters[ind].minValue.subscribe(runFilter);
				}
				if ('maxValue' in filter)
				{
					self.filtersViewModel.filters[ind].maxValue.subscribe(runFilter);
				}


			});

			//iterate over the edit fields
			$.each(self.viewModel.editFields, function(ind, field)
			{
				//if there are constraints to maintain, set up the subscriptions
				if (field.constraints && self.getObjectSize(field.constraints))
				{
					//we want to subscribe to changes on the OTHER fields since that's what defines changes to this one
					$.each(field.constraints, function(key, relationshipName)
					{
						var fieldIndex = ind,
							fieldName = field.field;

						self.viewModel[key].subscribe(function(val)
						{
							//when this value changes, we will want to update the listOptions for the other field
							//this shouldn't affect the currently-selected item
							var constraints = {};

							//iterate over this field's constraints
							$.each(field.constraints, function(key, relationshipName)
							{
								constraints[key] = self.viewModel[key]();
							});

							//freeze the actions
							self.viewModel.freezeActions(true);
							self.viewModel.editFields[fieldIndex].loadingOptions(true);

							$.ajax({
								url: base_url + self.viewModel.modelName() + '/update_options/',
								type: 'POST',
								dataType: 'json',
								data: {
									constraints: constraints,
									type: 'edit',
									field: fieldName,
									selectedItems: self.viewModel[fieldName]()
								},
								complete: function()
								{
									self.viewModel.freezeActions(false);
									self.viewModel.editFields[fieldIndex].loadingOptions(false);
								},
								success: function(response)
								{
									//update the options
									self.viewModel.listOptions[fieldName](response);
								}
							});
						});
					});
				}
			});

			//subscribe to page change
			self.viewModel.pagination.page.subscribe(function(val)
			{
				self.viewModel.page(val);
			});

			//subscribe to rows per page change
			self.viewModel.rowsPerPage.subscribe(function(val)
			{
				self.viewModel.updateRowsPerPage(parseInt(val));
			});
		},

		/**
		 * Inits the page events
		 */
		initEvents: function()
		{
			var self = this;

			//clicking the new item button
			$('#content').on('click', 'div.results_header a.new_item', function(e)
			{
				e.preventDefault();
				History.pushState({modelName: self.viewModel.modelName(), id: 0}, null, route + self.viewModel.modelName() + '/new');
			});


			//set up the history event callback
			History.Adapter.bind(window,'statechange',function() {
				var state = History.getState();

				//if the ignore key is true, or if this is the inital state, exit out.
				if (state.data.ignore || (state.data.init && !self.historyStarted))
					return;


				//if the model name is present
				if ('modelName' in state.data)
					//if that model name isn't the current model name, we are updating the model
					if (state.data.modelName !== self.viewModel.modelName())
						//get the new model
						self.viewModel.getNewModel(state.data);

				//if the state data has an id field and if it's not the active item
				if ('id' in state.data)
				{
					//get the new item (this includes when state.data.id === 0, which means it should be a new item)
					if (state.data.id !== self.viewModel.activeItem())
						self.viewModel.getItem(state.data.id);
				}
				else
				{
					//otherwise, assume that the user wants to be taken back to the results page. close the form
					self.viewModel.clearItem();
				}
			});
		},

		/**
		 * Sets up the push state's initial state
		 */
		initHistory: function()
		{
			var historyData = {
					modelName: this.viewModel.modelName(),
					init: true
				},
				uri = route + this.viewModel.modelName();

			//if the admin data had an id supplied, it means this is either the edit page or the new item page
			if ('id' in adminData)
			{
				this.viewModel.getItem(adminData.id);
				historyData.id = adminData.id;
				uri += '/' + (historyData.id ? historyData.id : 'new');
			}

			//now call the same to trigger the statechange event
			History.pushState(historyData, null, uri);

			this.historyStarted = true;
		},

		/**
		 * Initializes the computed observables
		 */
		initComputed: function()
		{
			//pagination information
			this.viewModel.pagination.isFirst = ko.computed(function()
			{
				return this.pagination.page() == 1;
			}, this.viewModel);

			this.viewModel.pagination.isLast = ko.computed(function()
			{
				return this.pagination.page() == this.pagination.last();
			}, this.viewModel);

		},

		/**
		 * Helper to get an object's size
		 *
		 * @param object
		 *
		 * @return int
		 */
		getObjectSize: function(obj)
		{
			var size = 0, key;

			for (key in obj)
			{
				if (obj.hasOwnProperty(key)) size++;
			}

			return size;
		}
	};


	//set up the admin instance
	$(function() {
		if ($('#admin_page').length)
			window.admin = new admin();
	});
})(jQuery);