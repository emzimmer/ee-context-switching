import React, { useState } from 'react'

export default function TemplateContext() {

    /**
     * Handle quick console messages
     */
    const debug = false;

    const debugLog = (...message) => {

        if ( debug ) {

            console.log(...message);
        }
    }


    /**
     * Stateful variables
     */
    const [isVisible, setVisibility] = useState( false );
    const [selectDisplay, setSelectDisplay] = useState( [editorEnhancerSettings.post_name, editorEnhancerSettings.post_id] );
    const [currentContext, setCurrentContext] = useState(() => {

        if ( editorEnhancerSettings.context_templates !== undefined ) {

            let nonce = false;
            let isInner = null;

            // i will represent pages, templates, and reusable_parts
            for ( let i in editorEnhancerSettings.context_templates ) {

                for ( let j in editorEnhancerSettings.context_templates[i] ) {

                    if ( editorEnhancerSettings.context_templates[i][j].id == editorEnhancerSettings.post_id ) {
                        nonce = editorEnhancerSettings.context_templates[i][j].nonce;
                        isInner = editorEnhancerSettings.context_templates[i][j].isInner;
                        break;
                    }
                }

                if ( nonce ) break;
            }

            return {
                postId: editorEnhancerSettings.post_id,
                title : editorEnhancerSettings.post_name,
                nonce : nonce,
                isInner : isInner
            }
        }

        return false;
    });


    /**
     * List creation
     */
    const createList = (templateTitle, templateType) => {

        const templates = editorEnhancerSettings.context_templates !== undefined ? editorEnhancerSettings.context_templates : false;

        if ( templates && Object.keys( templates[templateType] ).length > 0 ) {

            return (
                <div className='ee-context-group'>
                    <div className='ee-context-heading'>{templateTitle}</div>
                    <ul>
                        {Object.keys( templates[templateType] ).map( name =>
                            <li key={name}
                                template-name={name}
                                onClick={() => loadComponentsTree(
                                    templates[templateType][name].id,
                                    templates[templateType][name].nonce,
                                    templates[templateType][name].title,
                                    templates[templateType][name].isInner,
                                    templateType
                                )}
                            >
                                {templates[templateType][name].title}
                            </li>
                        )}
                    </ul>
                </div>
            )
        }

        return null;
    }

    // Create the lists as possible states
    const [csTemplates, setCsTemplates] = useState( () => createList( 'Templates', 'templates' ) );
    const [csReusables, setCsReusables] = useState( () => createList( 'Reusable Parts', 'reusable_parts' ) );
    const [csPages, setCsPages] = useState( () => createList( 'Pages', 'pages' ) );


    /**
     * Removal setup
     */
    // Set it up. It is reset in the tree loader function
    let removalOrder = [];

    // Removal setup function
    const setRemovalOrder = component => {

        // Gather child IDs first
        if ( component.children !== undefined ) {

            for ( var childComponentId in component.children ) {
                
                //debugLog(component.children[childComponentId]);
                setRemovalOrder( component.children[childComponentId] );
            }
        }

        // Skip body
        if ( component.id > 0 ) {

            // Then add its own id
            removalOrder.push({
                id:       component.id,
                name:     component.name,
                parentId: component.options.ct_parent
            });
        }
    }


    /**
     * Check for unsaved changes
     */
    const proceedWithUnsavedChanges = () => {

        if ( document.getElementById( 'ct-artificial-viewport' ).contentWindow.onbeforeunload !== null ) {

            // Give the user a choice
            return confirm( 'Unsaved changes will be lost. Do you wish to proceed?' );
        }

        return true;
    }

    /////
    const removeComponents = removalOrder => {

        for ( const i in removalOrder ) {
    
            if (
                    removalOrder[i].id == 0 ||              // Don't remove the body / inner content
                    removalOrder[i].id == 99999 ||          // Don't remove the component-template divider
                    removalOrder[i].name == 'oxy_header' ||   // Don't remove oxy_header; it is automatically removed when last oxy_header_row is removed
                    (
                        removalOrder[i].name == 'ct_div_block'
                        && $scope.iframeScope.component.options[removalOrder[i].parentId].name == 'oxy_dynamic_list'
                    ) // Don't remove the last remaining div in a repeater
            ) continue;

            // debugLog(removalOrder[i].id,
            //     removalOrder[i].name,
            //     removalOrder[i].parentId);

            try {
                
                $scope.iframeScope.removeComponentWithUndo(
                    removalOrder[i].id,
                    removalOrder[i].name,
                    removalOrder[i].parentId
                );
            }

            catch ( err ) {
                debugLog( err );
            }

            // Set empty array for component classes
            $scope.iframeScope.componentsClasses[removalOrder[i].id] = [];
        
            // Delete-ish
            $scope.iframeScope.component.options[removalOrder[i].id] = {
                model:{}
            };

        }
    }


    /**
     * Set inner content data based on whether the template is inner or not
     * 
     * 1.   innerContentRoot = null
     *      This will be rebuilt when new context is loaded
     * 
     * 2.   innerContentAdded
     *      Check this, which will exist as true if inner content component was ever added to the template
     * 
     * 3.   isInnerContent = false
     *      Important to render this as false, but would be a good idea to set and reset as needed
     * 
     * 4.   document.body.classList.remove('ct_inner')
     *      If the class 'ct_inner' exists, then Oxygen will display as Inner Content instead of Body
     */
    const setInnerData = isInner => {

        // Check if incoming component tree is NOT inner content
        if ( ! isInner  ) {

            // Remove ct_inner class from body elements in both interface and viewport
            document.body.classList.remove('ct_inner');
            // jQuery("iframe").contents().find("body").removeClass("ct_inner");
            $scope.iframeScope.csBodyClass();
            $scope.iframeScope.innerContentRoot = null;
            $scope.iframeScope.isInnerContent = false;
        }

        // The incoming tree IS inner content
        else {

            // Add ct_inner class to body elements in both interface and viewport
            document.body.classList.add('ct_inner');
            $scope.iframeScope.csBodyClass(true);
            $scope.iframeScope.isInnerContent = true;
        }
    }


    /**
     * Remove reusable CSS
     */
    const removeReusableCSS = currentPostId => {

        if ( $scope.iframeScope.postsData.length > 0 ) {

            Object.keys( $scope.iframeScope.postsData ).map( ( postId, data ) => {

                // Delete the CSS
                $scope.iframeScope.deleteCSSStyles('ct-re-usable-styles-' + postId);

                // Delete the data
                delete $scope.iframeScope.postsData[postId];

                // Reset the data if not for the current post id
                // This would mean the user is currently editing one of the reusable parts,
                // which shouldn't have saved css output
                if ( currentPostId !== parseInt( postId ) ) {
                    $scope.iframeScope.loadPostData( $scope.iframeScope.addReusableContent, parseInt( postId ) );
                }
            });
        }
    }

    
    /**
     * Update overlay text
     */
    const updateOverlay = ( text, display = false ) => {

        const overlay = jQuery('#ee-context-switching-overlay');

        overlay.text( text + '...' );

        if ( display ) {
            overlay.css( 'display', display );
        }
    }


    /**
     * Error handling
     */
    const handleError = err => {

        // Output the error notices and clear overlays
        const errorMessage = 'Error: ' + err + '. Do not save. Reload the page.';

        $scope.contextSwitched = false;
        setVisibility(false);
        debugLog( errorMessage );
        $scope.iframeScope.showErrorModal( 0, 'Error switching context:', err );
        updateOverlay( errorMessage, 'none' );
        $scope.overlaysCount = 1;
        $scope.hideLoadingOverlay();

        // Then run it backwards and reset to the original context
        loadComponentsTree( currentContext.postId, currentContext.nonce, currentContext.title, currentContext.isInner );

        return;
    }


    /**
     * Load the components tree
     */
    const loadComponentsTree = ( postId, nonce, title, isInner, templateType ) => {

        // Hide drop down menu immediately. No sense in dragging it out.
        jQuery('#ee-context-switching').css('display','none');
        
        // Stop immediately if user requests it, unless no saved changes exist
        if ( ! proceedWithUnsavedChanges() ) return;

        // Reset the removal order
        removalOrder = [];
        
        $scope.showLoadingOverlay('eeContextSwitching');
        updateOverlay( 'Loading data', 'flex' );
        
        // Try setting the removal object. If it fails, reset removalOrder and exit with error.
        // If this doesn't get set properly, then nothing will work anyway
        try {

            debugLog( 'Trying setRemovalOrder()' );

            // Also show the overlay and make sure the text is correct
            // updateOverlay( 'Preparing context', 'flex' );

            // Set removal order
            setRemovalOrder( $scope.iframeScope.componentsTree );
        }

        catch ( err ) {
            handleError(err);
            return;
        }

        // So, the removal order was set! Proceed.
        finally {

            setTimeout(() => {

                debugLog( 'Finally...' );

                // Now, try to remove all of the components
                try {

                    // Remove components in order
                    if ( removalOrder.length > 0 ) {

                        debugLog( 'removalOrder.length is greater than 0' );
                        
                        try {
                            
                            debugLog( 'Try removeComponents()' );
                            updateOverlay( 'Removing current context' );
                            removeComponents( removalOrder );
                        }

                        catch ( err ) {
                            handleError(err);
                            return;
                        }
                    }

                    // Removal order has no items
                    else {
                        debugLog( 'The current context has no components to remove.' );
                    }
                }

                catch ( err ) {
                    handleError(err);
                    return;
                }

                finally {

                    debugLog( 'Finally...' );

                    // Determine and set inner data
                    setInnerData( isInner );

                    // Set the AJAX data
                    var data = {
                        action : 'ct_get_components_tree',
                        id : postId,
                        post_id : postId,
                        nonce : nonce
                    };

                    // Include information about inner content
                    if ( jQuery('body').hasClass('ct_inner') ) {
                        data['ct_inner'] = true;
                    }

                    // Send AJAX request
                    jQuery.ajax({
                        url : editorEnhancerSettings.ajaxurl,
                        method : "POST",
                        data : data,
                        transformResponse: false,
                    })

                    // Successful
                    .success( function( data, status, headers, config ) {

                        debugLog( 'AJAX success. Building...' );
                        // updateOverlay( 'Building new context' );

                        try {

                            updateOverlay( 'Applying new context' );

                            // Parse the response
                            var response = JSON.parse( data );

                            // Safety net for blank templates
                            if ( response.children == undefined ) {
                                response.children = [];
                            }

                            // Update postID references
                            $scope.iframeScope.ajaxVar.postId = postId;
                            CtBuilderAjax.postId = postId;
                            editorEnhancerSettings.post_id = postId;

                            // Update nonce references
                            $scope.iframeScope.ajaxVar.nonce = nonce;
                            CtBuilderAjax.nonce = nonce;

                            // Run the builderInit. Parameter is the tree from response.
                            $scope.iframeScope.builderInit( response );
                        }

                        // Error
                        catch ( err ) {
                            handleError(err);
                            return;
                        }

                        // After built, reset the other data
                        finally {

                            debugLog( 'Finally...' );

                            // Set editable selector to false
                            $scope.iframeScope.selectorToEdit = false;

                            // Remove all reusable styles and let Oxygen rebuild them
                            removeReusableCSS(postId);

                            try {

                                debugLog( 'Trying cleanup.' );

                                // updateOverlay( 'Cleaning resources' );

                                // Set a flag to skip the recent changes caused by data restore
                                $scope.iframeScope.skipChanges = true;

                                // Clear out the undo/redo history
                                $scope.iframeScope.undoManager.clear();

                                // Activate the root
                                $scope.iframeScope.activateComponent( 0, 'root' );

                                // Notify other extensions
                                // ... And give them three seconds to run their updates
                                $scope.contextSwitched = true;

                                //
                                // $scope.iframeScope.rebuildDOM();
                            }

                            catch ( err ) {
                                debugLog( err );
                            }

                            finally {

                                setTimeout(() => {

                                    $scope.contextSwitched = false;
                                    
                                    // Reset drop down visibility
                                    setVisibility(false);

                                    // Set the drop down selector option
                                    setSelectDisplay( [title, postId] );

                                    // Hide overlays
                                    // $scope.overlaysCount = 1;
                                    $scope.hideLoadingOverlay('eeContextSwitching');
                                    updateOverlay( 'Done!', 'none' );
                                    
                                    debugLog( 'All done!' );

                                    // Set the new current context
                                    setCurrentContext({
                                        postId: postId,
                                        title: title,
                                        nonce: nonce,
                                        isInner: isInner
                                    });

                                    // Pages need this so that their dynamic data can be reset
                                    if ( 'pages' === templateType ) {
                                        $scope.iframeScope.loadTemplatesTerm( postId, 'post' );

                                        // In case the preview panel exists, we don't want users to play with both preview and context
                                        // when editing pages.
                                        jQuery('.oxygen-toolbar-panels > .oxygen-toolbar-panel:first-child > .oxygen-control-wrapper').css({'display':'none'});

                                    // It's not a page, and may be coming to a template. If so, need to re-enable the preview dropdown.
                                    // This won't resolve if page was the first item open, however, as it won't exist.
                                    } else {
                                        jQuery('.oxygen-toolbar-panels > .oxygen-toolbar-panel:first-child > .oxygen-control-wrapper').css({'display':'flex'});

                                    }

                                }, 3000);
                            }
                        }
                    })

                    // Error
                    .error(function(data, status, headers, config) {
                        handleError( 'Data: ' + data + '. Status: ' + status );
                    });
                }

            // End of setTimeout
            }, 100);
        }
    }

    const refreshContexts = () => {

        const data = {
            action: 'ee_refresh_contexts'
        };

        // Send the data
        jQuery.ajax({
            url: editorEnhancerSettings.ajaxurl,
            method: "GET",
            data: data
        })

        // Return the metaId for the post meta
        .success(function(response,status,headers,config) {

            if ( ! response ) {
                return;
            }

            editorEnhancerSettings.context_templates = JSON.parse(response);

            setCsTemplates( () => createList( 'Templates', 'templates' ) );
            setCsReusables( () => createList( 'Reusable Parts', 'reusable_parts' ) );
            setCsPages( () => createList( 'Pages', 'pages' ) );
            // console.log(response);
            // console.log(JSON.parse(response));
        });
    }


    /**
     * Return
     */
    return (
        <>
            <span>Context</span>
            <div>
                <div onClick={() => setVisibility(!isVisible)}>
                    <a title='Go to admin page' href={editorEnhancerSettings.admin_url + '/post.php?action=edit&post=' + selectDisplay[1]}>
                        <i className='fa fa-external-link'></i>
                    </a>
                    <span>{selectDisplay[0]}</span>
                    {editorEnhancerSettings.context_switching &&
                        <i className='fa fa-caret-down'></i>
                    }
                </div>

                {isVisible && editorEnhancerSettings.context_switching ?

                    <div id='ee-context-switching'>
                        <div
                            className="refresh-context-options"
                            title="Click to refresh contexts."
                            onClick={refreshContexts}
                        >
                            <i className="fa fa-refresh"></i>
                        </div>
                        {csTemplates}
                        {csReusables}
                        {csPages}
                    </div>

                    : null
                }
            </div>
            <div id="ee-context-switching-overlay">
                "Removing current context..."
            </div>
        </>
    )
}