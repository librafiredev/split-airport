/* globals icl_ajx_url */

/**
 * Created by andrea.
 * Date: 23/01/14
 * Time: 17:28
 */

jQuery( function ( $ ) {
  setupCopyButtons()

  const connectTranslations = function () {
    var postEdit = postEdit || {}

    postEdit.$connect_translations_dialog = $( '#connect_translations_dialog' )
    postEdit.$no_posts_found_message = postEdit.$connect_translations_dialog.find( '.js-no-posts-found' )
    postEdit.$posts_found_container = postEdit.$connect_translations_dialog.find( '.js-posts-found' )
    postEdit.$ajax_loader = postEdit.$connect_translations_dialog.find( '.js-ajax-loader' )
    postEdit.$connect_translations_dialog_confirm = $( '#connect_translations_dialog_confirm' )

    postEdit.connect_element_translations_open = function ( event ) {
      if ( typeof ( event.preventDefault ) !== 'undefined' ) {
        event.preventDefault()
      } else {
        event.returnValue = false
      }

      postEdit.$connect_translations_dialog.find( '#post_search' ).val( '' )
      postEdit.$connect_translations_dialog.find( '#assign_to_trid' ).val( '' )
      postEdit.$connect_translations_dialog.dialog( 'open' )
      postEdit.connect_element_translations_data()
    }

    postEdit.connect_element_translations_data = function () {
      const $connect_translations_dialog_selector = $( '#post_search', postEdit.$connect_translations_dialog )

      const trid = $( '#icl_connect_translations_trid' ).val()
      const post_type = $( '#icl_connect_translations_post_type' ).val()
      const source_language = $( '#icl_connect_translations_language' ).val()
      const nonce = $( '#_icl_nonce_get_orphan_posts' ).val()
      const data = 'icl_ajx_action=get_orphan_posts&source_language=' + source_language + '&trid=' + trid + '&post_type=' + post_type + '&_icl_nonce=' + nonce

      postEdit.$ajax_loader.show()

      const request = $.ajax( {
        type: 'POST',
        url: icl_ajx_url,
        dataType: 'json',
        data: data
      } )

      request.done( function ( posts ) {
        const $assignPostButton = $( '.js-assign-button' )

        if ( posts.length > 0 ) {
          postEdit.$posts_found_container.show()
          postEdit.$no_posts_found_message.hide()
          $assignPostButton.prop( 'disabled', false )

          $connect_translations_dialog_selector.autocomplete( {
            minLength: 0,
            source: posts,
            focus: function ( event, ui ) {
              $connect_translations_dialog_selector.val( ui.item.label )
              return false
            },
            select: function ( event, ui ) {
              $connect_translations_dialog_selector.val( ui.item.label )
              $( '#assign_to_trid' ).val( ui.item.value )
              return false
            }
          } )
            .focus()
            .data( 'ui-autocomplete' )._renderItem = function ( ul, item ) {
              return $( '<li>' )
                .append( jQuery( '<a></a>' ).text( item.label ) )
                .appendTo( ul )
            }
        } else {
          postEdit.$posts_found_container.hide()
          postEdit.$no_posts_found_message.show()
          $assignPostButton.prop( 'disabled', true )
        }
      } )

      request.always( function () {
        postEdit.$ajax_loader.hide() // Hide ajax loader always, no matter if ajax succeed or not.
      } )
    }

    postEdit.connect_element_translations_init = ( function () {
      postEdit.$connect_translations_dialog.dialog(
        {
          dialogClass: 'wpml-dialog otgs-ui-dialog',
          width: 'auto',
          modal: true,
          autoOpen: false,
          closeOnEscape: true,
          buttons: [
            {
              text: postEdit.$connect_translations_dialog.data( 'cancel-label' ),
              class: 'button button-secondary alignleft',
              click: function () {
                $( this ).dialog( 'close' )
              }
            }, {
              text: postEdit.$connect_translations_dialog.data( 'ok-label' ),
              class: 'button button-primary js-assign-button',
              click: function () {
                $( this ).dialog( 'close' )
                postEdit.connect_element_translations_do()
              }
            }
          ]
        }
      )
    }() ) // Auto executable function

    postEdit.connect_element_translations_do = function () {
      const trid = $( '#assign_to_trid' ).val()
      const post_type = $( '#icl_connect_translations_post_type' ).val()
      const post_id = $( '#icl_connect_translations_post_id' ).val()
      const nonce = $( '#_icl_nonce_get_posts_from_trid' ).val()

      const data = 'icl_ajx_action=get_posts_from_trid&trid=' + trid + '&post_type=' + post_type + '&_icl_nonce=' + nonce

      const request = $.ajax( {
        type: 'POST',
        url: icl_ajx_url,
        dataType: 'json',
        data: data
      } )

      request.done( function ( posts ) {
        if ( posts.length > 0 ) {
          const $list = $( '#connect_translations_dialog_confirm_list' )
          $list.empty()
          const $ul = $( '<ul />' ).appendTo( $list )

          let translation_set_has_source_language = false

          $.each( posts, function () {
            const $li = $( '<li>' ).append( '<span></span>' )
            $li.find( 'span' ).text( '[' + this.language + '] ' + this.title )
            $li.appendTo( $ul )
            if ( this.source_language && !translation_set_has_source_language ) {
              translation_set_has_source_language = true
            }
          } )

          const alert = $( '<p>' ).append( jQuery( '<strong></strong>' ).html( postEdit.$connect_translations_dialog.data( 'alert-text' ) ) )
          alert.appendTo( $list )

          const set_as_source_checkbox = $( '<input type="checkbox" value="1" name="set_as_source" />' )

          if ( !translation_set_has_source_language ) {
            set_as_source_checkbox.prop( 'checked', true )
          }
          const action = $( '<label>' ).append( set_as_source_checkbox ).append( postEdit.$connect_translations_dialog.data( 'set_as_source-text' ) )
          action.appendTo( $list )

          postEdit.$connect_translations_dialog_confirm.dialog(
            {
              dialogClass: 'wpml-dialog otgs-ui-dialog',
              resizable: false,
              width: 'auto',
              autoOpen: true,
              modal: true,
              buttons: [
                {
                  text: postEdit.$connect_translations_dialog_confirm.data( 'cancel-label' ),
                  class: 'button button-secondary alignleft',
                  click: function () {
                    $( this ).dialog( 'close' )
                    postEdit.$connect_translations_dialog.dialog( 'open' )
                  }
                }, {
                  text: postEdit.$connect_translations_dialog_confirm.data( 'assign-label' ),
                  class: 'button button-primary js-confirm-connect-this-post',
                  click: function () {
                    const $confirmButton = $( '.js-confirm-connect-this-post' )
                    $confirmButton.prop( 'disabled', true ).removeClass( 'button-primary' ).addClass( 'button-secondary' )

                    $( '<span class="spinner" />' ).appendTo( $confirmButton )

                    const nonce = $( '#_icl_nonce_connect_translations' ).val()

                    const data_object = {
                      icl_ajx_action: 'connect_translations',
                      post_id: post_id,
                      post_type: post_type,
                      new_trid: trid,
                      _icl_nonce: nonce,
                      set_as_source: ( set_as_source_checkbox.is( ':checked' ) ? 1 : 0 )
                    }

                    const request = $.ajax(
                      {
                        type: 'POST',
                        url: icl_ajx_url,
                        dataType: 'json',
                        data: data_object
                      }
                    )

                    request.done(
                      function ( result ) {
                        if ( result ) {
                          postEdit.$connect_translations_dialog.dialog( 'close' )
                          location.reload()
                        }
                      }
                    )
                  }
                }
              ]
            }
          )
        }
      }
      )
    }

    $( '#icl_document_connect_translations_dropdown' )
      .find( '.js-set-post-as-source' )
      .on( 'click', postEdit.connect_element_translations_open )
  }

  const setPostAsSource = $( '#icl_document_connect_translations_dropdown .js-set-post-as-source' )
  // Edit an existing post, just initialize the connectTranslations function.
  if ( setPostAsSource.length ) {
    connectTranslations()
  } else {
    // After publishing a new post, initialize the connectTranslations function because setPostAsSource isn't available.
    document.addEventListener( 'WPMLLanguagesMetaBoxesRefreshed', function () {
      connectTranslations()
    } )
  }

  /**
   * HOTFIX DIALOG BOX
   * Remove when WooCommerce does not include jquery-ui smoothness anymore
   **/
  const jQueryUI = $( '#jquery-ui-style-css[href*="smoothness"]' )
  let jQuerySmoothnessHref

  // if jquery ui smoothness css is loaded
  if ( jQueryUI.length ) {
    // click on Connect with translations
    $( 'body' ).on( 'click', '#icl_document_connect_translations_dropdown .js-set-post-as-source', function () {
      const connectDialog = $( '[aria-describedby="connect_translations_dialog"]' ); let intervalCheckDialog

      // abort if dialog does not exists
      if ( !connectDialog.length ) return false

      // backup href of jquery ui smoothness
      jQuerySmoothnessHref = jQueryUI.attr( 'href' )

      // remove jquery ui smoothness css
      jQueryUI.attr( 'href', '' )

      // check every 250ms if connect translations dialog is still open
      intervalCheckDialog = setInterval( function () {
        // if dialog is not open anymore
        if ( !connectDialog.is( ':visible' ) ) {
          if ( $( '.ui-widget-overlay' ).length == 0 ) {
            // reapply jquery ui smoothness css again
            jQueryUI.attr( 'href', jQuerySmoothnessHref )
            // stop interval
            clearInterval( intervalCheckDialog )
          }
        }
      }, 250 )
    } )
  }
  /* HOTFIX END */

  const classic_wp_editor_form = $( '#post' )
  let is_duplicate_post = typeof icl_duplicate_data !== 'undefined' && icl_duplicate_data.duplicate_post
  const classic_editor_duplicate_post_changed = classic_wp_editor_form.length && is_duplicate_post && icl_duplicate_data.wp_classic_editor_changed

  const post_form_callback = function ( e ) {
    if ( is_duplicate_post || classic_editor_duplicate_post_changed ) {
      e.preventDefault()
      const answer = window.confirm( icl_duplicate_data.icl_duplicate_message )
      const spinner = $( '#publishing-action .spinner' )
      if ( answer ) {
        spinner.toggleClass( 'is-active' )
        $.ajax( {
          method: 'POST',
          url: ajaxurl,
          data: {
            action: 'check_duplicate',
            post_id: icl_duplicate_data.duplicate_post,
            icl_duplciate_nonce: icl_duplicate_data.duplicate_post_nonce
          }
        } )
          .success( function ( res ) {
            spinner.toggleClass( 'is-active' )
            if ( res.data ) {
              is_duplicate_post = false
              classic_wp_editor_form.submit()
            } else {
              alert( icl_duplicate_data.icl_duplicate_fail )
            }
          } )
          .error( function () {
            spinner.toggleClass( 'is-active' )
            alert( icl_duplicate_data.icl_duplicate_fail )
          } )
      }
    }
  }

  classic_wp_editor_form.on( 'submit', post_form_callback )
  $( document ).on( 'click', '.editor-post-publish-button', post_form_callback )
  $( document ).on( 'click', '.editor-post-saved-state.is-saving', post_form_callback )
  $( document ).on( 'heartbeat-send', function ( event, data ) {
    data.icl_post_language = $( '#icl_post_language' ).val()
    data.icl_trid = $( 'input[name="icl_trid"]' ).val()
  } )
} )

function setupCopyButtons () {
  jQuery( '#icl_translate_independent' ).click( function () {
    jQuery( this ).attr( 'disabled', 'disabled' ).after( icl_ajxloaderimg )
    jQuery.ajax( {
      type: 'POST',
      url: icl_ajx_url,
      data: 'icl_ajx_action=reset_duplication&post_id=' + jQuery( '#post_ID' ).val() + '&_icl_nonce=' + jQuery( '#_icl_nonce_rd' ).val(),
      success: function ( msg ) {
        location.reload( true )
      }
    } )
  } )
  jQuery( '#icl_set_duplicate' ).click( function () {
    if ( confirm( jQuery( this ).next().html() ) ) {
      jQuery( this ).attr( 'disabled', 'disabled' ).after( icl_ajxloaderimg )
      const icl_set_duplicate = jQuery( '#icl_set_duplicate' )
      const wpml_original_post_id = icl_set_duplicate.data( 'wpml_original_post_id' )
      const post_lang = icl_set_duplicate.data( 'post_lang' )
      jQuery.ajax( {
        type: 'POST',
        url: icl_ajx_url,
        data: 'icl_ajx_action=set_duplication&wpml_original_post_id=' + wpml_original_post_id + '&_icl_nonce=' + jQuery( '#_icl_nonce_sd' ).val() + '&post_lang=' + post_lang,
        success: function ( msg ) {
          location.replace(
            location.href.replace( 'post-new.php', 'post.php' ).replace( /&trid=([0-9]+)/, '' ) + '&post=' + msg.data.id + '&action=edit' )
        }
      } )
    }
  } )
}
