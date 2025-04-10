define([
    'jquery',
    'knockout',
    'Magento_Ui/js/form/element/abstract',
    'mage/translate',
    'Magezon_Core/js/mage/browser'
], function ($, ko, Abstract, Element, $t) {
    'use strict';

    var Marker = function(data) {
        this.marker_id    = ko.observable(data.marker_id);
        this.marker_label = ko.observable(data.marker_label ? data.marker_label : '');
        this.title        = ko.observable(data.title ? data.title : 'New Marker');
        this.description  = ko.observable(data.description);
        this.left         = ko.observable(data.left);
        this.top          = ko.observable(data.top);
        this.sku          = ko.observable(data.sku);
        this.modal        = ko.observable(data.modal ? data.modal : false);
        this.products     = ko.observableArray([]);
        this.locations    = ko.observableArray([{ Location:"Top" , Value : "top" }, { Location:"Left" , Value : "left" }, { Location:"Bottom" , Value : "bottom" }, { Location:"Right" , Value : "right" }]);
        this.popup        = ko.observable(data.popup ? data.popup : 'bottom');
        this.marginTop    = ko.observable(data.marginTop ? data.marginTop : '0');
        this.animation    = ko.observable(data.animation ? data.animation : 'none');
        var fields        = ['marker_label', 'title', 'description', 'left', 'top', 'sku', 'popup'];

		var self = this;
		for(var key in this) {
			if (key === 'sku') {
				this[key].subscribe(function(value) {
					if (value.length >= 3) {
                        var url = window.location.origin + '/admin/lookbook/profile/loadproduct';
						$('body').trigger('markerBuilderUpdated');
						$('#products').addClass('lookbook-btn-loading');
						$.ajax({
			           		type: "POST",
				           	url: url,
				           	data: {
				           		sku: value
				           	},
				           	success: function(data) {	
				           		$('#products').removeClass('lookbook-btn-loading');
				               	$( "#products" ).autocomplete({
							      	source: data,
							      	appendTo: ".mgz-marker"
							    });
				           	}
			         	});
					} 
					else {
						$( "#products" ).autocomplete({
					      	source: []
					    });
					}
				});
			} else {
				if (fields.indexOf(key) >= 0) {
					this[key].subscribe(function() {
						let key2 = key;
						$('body').trigger('markerBuilderUpdated');
					});
				}
			}
		} 
	}

    return Abstract.extend({
    	defaults: {
    		elementTmpl: 'Magezon_LookBook/form/element/marker',
    		markerTmpl: 'Magezon_LookBook/form/element/marker1',
    		template: 'Magezon_LookBook/form/element/file-manager',
    		imgWidth: 800,
    		imgHeight: 800,
    		items: [],
    		listens: {
    			items: 'changeItems'
    		},
    		imports: {
    			imageSrc: '${ $.provider }:data.image'
    		},
    		modules: {
    			image:  'ns = ${ $.ns }, index = image'
    		},
            
    		chooseBtnLabel: 'Select Image',
    		tmp: null,
            showPreview: false,
            fileUrl: '',
            fileName: '',
            fileSize: '',
            previewHeight: '',
            previewWidth: '',

            tracks: {
                showPreview: true,
                fileUrl: true,
                fileName: true,
                fileSize: true,
                previewHeight: true,
                previewWidth: true,
            }
    	},

    	initialize: function () {
    		this._super();
    		_.bindAll(this, 'YourAfterRenderFunction', 'openModal', 'removeMarker');
    		var self = this;
    		var items = [];

    		$('body').on('markerBuilderUpdated', function() {
    			self.updateValue();
    		});
            
            if (this.value()) {
            	_.each(JSON.parse(this.value()), function(item) {
	            	items.push(new Marker(item));
	            });
            }
            
            this.items(items);
            _.map(self.items(), function(_marker, index) {
				_marker.modal(false);
			});

            if (this.tmp && this.tmp.name) {
                this.fileSize    = this.formatSize(this.tmp.size);
                this.fileName    = this.processFileName(this.tmp.name);
                this.fileUrl     = this.tmp.url;
                this.showPreview = true;
            };

           	if (this.imageSrc) {
           		this.imgSrc(window.mgzMediaUrl + this.imageSrc);
           	}

            console.log(this.searchUrl);

    		return this;
    	},

    	/**
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super();
            this.observe(['imgSrc', 'dragged', 'imgWidth', 'imgHeight', 'result']);

            this.observe({
            	items: []
            });
            return this;
        },

        updateValue: function() {
        	var self = this;

        	var resultVal1 = self.items();
        	var resultVal = [];
        	var fields = ['marker_label', 'title', 'description', 'left', 'top', 'sku', 'popup'];

        	_.map(resultVal1, function(_marker, index) {        		
        		var subresult = {};
        		_.map(_marker, function(_markerChild, indexChild) {
        			if (fields.indexOf(indexChild) >= 0) {
        				subresult[indexChild] = _marker[indexChild]._latestValue;
        			}
        		});
        		resultVal.push(subresult);
			});

        	self.result(ko.toJSON(resultVal));
        	self.value(ko.toJSON(resultVal));
        },

        changeItems: function() {
        	var self = this;
        	self.updateValue();
        },

    	saveMarker: function(marker) {

		},

		addMarker: function() {
			var code   = Math.random() * 10 + 5 | 0;
			var self   = this;
			var marker = new Marker({marker_id: self.uniqueid(), marker_label: '', title: 'Item', description: '', left: 68 - code, top: 68 - code, sku: '', selectedLocation: 'bottom'});
			self.items.push(marker);

			_.map(self.items(), function(_marker, index) {
				if (marker !== _marker) {
					_marker.modal(false);
				}
			});
			marker.modal(!marker.modal());
            
			self.dragg();
			$( "#products" ).autocomplete({
		      	source: []
		    });
		},

		deleteImage: function() {
			var self = this;
			self.imgSrc('');
			self.items.removeAll();
			this.image().value('');
		},

		removeMarker: function(marker) {
			var self = this;
            marker.marginTop('0');
			self.items.remove(marker);
			self.value(ko.toJSON(self.items()));
			self.result(ko.toJSON(self.items()));
		},

		openModal: function(marker) {
			var self = this;
			_.map(self.items(), function(_marker, index) {
				if (marker !== _marker) {
					_marker.modal(false);
                    _marker.marginTop('0');
				}
			});
            marker.marginTop('-10px');
			marker.modal(marker.modal(true));
			$( "#products" ).autocomplete({
		      	source: []
		    });
		},

		closeMarker: function(marker) {
			marker.modal(false);
            marker.marginTop('0');
		},

        cancelMarker: function(marker) {
            marker.modal(false);
            marker.marginTop('0');
        },

		YourAfterRenderFunction: function(elem, marker) {
			var self = this;
			if ($('.content').children().length === self.items().length) {
			 	self.dragg();
			}
		},

		uniqueid: function (size) {
	        var code = Math.random() * 25 + 65 | 0,
	            idstr = String.fromCharCode(code);

	        size = size || 12;

	        while (idstr.length < size) {
	            code = Math.floor(Math.random() * 42 + 48);

	            if (code < 58 || code > 64) {
	                idstr += String.fromCharCode(code);
	            }
	        }

	        return idstr.toLowerCase();
	    },

        enableDetails: function(marker) {
            marker.animation('1.5s linear 0s infinite normal lookbook-pulse');
        },

        disableDetails: function(marker) {
            marker.animation('none');
        },

	    dragg: function() {
	    	var self = this;
			$( '#' + self.uid + " .draggable" ).draggable({
				containment: ".content",
				scroll: false,
				create: function( event, ui ) {
					var $this = $(this);
				},
				start: function( event, ui ) {
					var marker = ui.helper[0].className;
					self.dragged(true);
				},
				drag: function( event, ui ) {
					var $this = $(this);
		            var thisPos = $this.position();
		            var parentPos = $this.parent().position();

					var x        = thisPos.left - parentPos.left;
					var y        = thisPos.top - parentPos.top;

		            var marker = ko.contextFor($(ui.helper)[0]).$data;
		            marker.left(x);
					marker.top(y);

					_.map(self.items(), function(_marker, index) {
						if (marker !== _marker) {
							_marker.modal(false);
						}
					});
					marker.modal(true);
				},
				stop:function( event, ui ) {
					self.dragged(false);
					var marker = ko.contextFor($(ui.helper)[0]).$data;
					marker.modal(!marker.modal());
				}
			});
		},
		
		/**
         * Removes provided file from thes files list.
         *
         * @param {Object} file
         * @returns {FileUploader} Chainable.
         */
        chooseImage: function () {
            if (!this.disabled()) {
                window.MgzMediabrowserUtility.openDialog(window.mgzFilesBrowserWindowUrl + 'target_element_id/' + this.uid  + '-input/', false, false, 'Insert Image', {closed: function() { jQuery('#mceModalBlocker').show()}})
            }
            var self = this;
            var code   = Math.random() * 10 + 5 | 0;
            _.map(self.items(), function(_marker, index) {
				_marker.modal(false);
				_marker.left(code);
				_marker.top(code);
			});
            return this;
        },

        updatedImage: function (elem, event) {
        	var value = $('#' + this.uid + '-input').val();
        	var self = this;

        	var imgSrc = window.mgzMediaUrl + value;
        	this.imgSrc(imgSrc);

			_.map(self.items(), function(_marker, index) {
				_marker.modal(false);
			});

			this.image().value(value);
        },

        /**
         * Formats incoming bytes value to a readable format.
         *
         * @param {Number} bytes
         * @returns {String}
         */
        formatSize: function (bytes) {
            var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'],
                i;

            if (bytes === 0) {
                return '0 Byte';
            }

            i = window.parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));

            return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
        },

        /**
         * Removes provided file from thes files list.
         *
         * @param {Object} file
         * @returns {FileUploader} Chainable.
         */
        removeFile: function (file) {
            if (!this.disabled()) {
                this.showPreview = false;
                this.value('');
            }
            return this;
        },

        /**
         * Handler of the preview image load event.
         *
         * @param {Object} file - File associated with an image.
         * @param {Event} e
         */
        onPreviewLoad: function (elem, event) {
            var img            = event.currentTarget;
            this.imgWidth(img.naturalWidth);
			this.imgHeight(img.naturalHeight);
        },

        /**
         * Update the preview, submit ajax get file size
         */
        reloadImage: function(elem, event) {
            var self  = this;
            var input = event.currentTarget;
            var file  = $(input).val();
            if (file) {
                this.showPreview = true;
                this.fileUrl     = window.mgzMediaUrl + file;
                this.fileName    = this.processFileName(file);
                $.ajax({
                    type: 'HEAD',
                    url: this.fileUrl,
                    complete: function(xhr) {
                        var size = self.formatSize(xhr.getResponseHeader('Content-Length'));
                        if (size) {
                            self.fileSize = size;
                        } else {
                            self.fileSize = '';
                        }
                    }
                });
            }
        },

        /**
         * Convert real image, remove wysiwyg
         */
        processFileName: function(name) {
            var nameE = name.split("/");
            return nameE[nameE.length-1];
        }
    });

});