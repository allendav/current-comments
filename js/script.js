/*
 * Current Comments - script.js
 */


jQuery( function( $ ) {
  Current_Comments = {};

	Current_Comments.Models = {
		Comment: Backbone.Model.extend( {
			defaults: {
				author : '',
				author_url : '',
				post_title : '',
				permalink : '',
				comment_date_gmt : 0
			}
		} )
	},

	Current_Comments.Collections = {
		Comments: Backbone.Collection.extend( {
			model: Current_Comments.Models.Comment,

			comparator: function( comment1, comment2 ) {
				return ( comment1.get( 'id' ) > comment2.get( 'id' ) ) ? -1 : 1;
			},

			/* WP 3.5 uses Backbone.js 0.9.2, which lacks some interesting features */
			/* like collection.add( merge : true ) so we will use this method to fresh the collection */
			/* WP 3.6 should be at least Backbone.js 1.0.0 */
			/* http://stackoverflow.com/questions/8211888/merge-backbone-collection-with-server-response */
			freshen: function( objects ) {
				var model;
				// Mark all for removal
				this.each( function( m ) {
					m._remove = true;
				});
				// Apply each object
				_( objects ).each( function( attrs ) {
					model = this.get( attrs.id );
					if ( model ) {
						model.set( attrs ); // existing model
						delete model._remove
					} else {
						this.add( attrs ); // new model
					}
				}, this );
				// Now check for any that are still marked for removal
				var toRemove = this.filter( function( m ) {
					return m._remove;
				})
				_( toRemove ).each( function( m ) {
					this.remove( m );
				}, this);
				this.trigger( 'freshen', this );
			}
		})
	},

	Current_Comments.Views = {
		Comment: Backbone.View.extend( {
			model: Current_Comments.Models.Comment,

			tagName: 'li',

			initialize: function() {
				this.model.on( 'change', this.render, this );
				this.model.on( 'destroy', this.remove, this );
			},

			render: function() {
				var template = _.template( "<a href='<%= author_url %>'><%= author %></a> on <a href='<%= permalink %>'><%= post_title %></a>" );
				this.$el.html( template( this.model.attributes ) );
				this.$el.addClass( 'current-comment-' + this.model.get( 'id' ) ); // so we can find it later (removeOne)

				var original_background_color = this.$el.css( "background-color" );
				this.$el.css( "background-color", "#e6e3c1" );
				this.$el.animate( { "background-color": original_background_color }, 3000 );

				return this;
			},

			remove: function() {
				this.$el.remove();
			}
		} ),

		Comments: Backbone.View.extend( {
			tagName: 'ul',

			initialize: function() {
				this.collection.on( 'add', this.addOne, this );
				this.collection.on( 'reset', this.addAll, this );
				this.collection.on( 'remove', this.removeOne, this );
			},

			render: function() {
				this.collection.forEach( this.addOne, this );
			},

			addOne: function( comment ) {
				var comment_view = new Current_Comments.Views.Comment( { model: comment } );

				var indexOf = this.collection.indexOf( comment );
				if ( 0 == indexOf ) {
					this.$el.prepend( comment_view.render().el );
				} else {
					this.$el.find( 'li:eq(' + (indexOf - 1) + ')' ).after( comment_view.render().el );
				}
			},

			addAll: function() {
				this.collection.forEach( this.addOne, this );
			},

			removeOne: function( comment ) {
				this.$el.find( '.current-comment-' + comment.id ).remove();
			}
		} )
	},

	Current_Comments.Apps = {
		App : {
			initialize: function( el, ajaxurl ) {
				this.collection = new Current_Comments.Collections.Comments();
				this.collection_view = new Current_Comments.Views.Comments( { collection: this.collection, el : el } );
				this.ajaxurl = ajaxurl;
				this.requestUpdate();
			},

			requestUpdate: function() {
				$.get( this.ajaxurl, { action: 'currcomm_read' }, function( data ) {
					// this.collection.add( data, { merge: true } ); // ack - merge is not in WP 3.5 (Backbone.js 0.9.2)
					this.collection.freshen( data );

					setTimeout( function() {
						this.requestUpdate();
					}.bind( this ), 5000 );
				}.bind( this ));
			}
		}
	}
});

jQuery( document ).ready( function( $ ) {
	var el = $( '.current-comments-container' );
	if ( el.length ) {
		var app = Current_Comments.Apps.App;
		app.initialize( el, Current_Comments_Ajax.url );
	}
});
