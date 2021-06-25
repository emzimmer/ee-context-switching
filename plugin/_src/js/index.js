/**
 * This is the root JS file. You can import other
 * JS files and create your program here. The 
 * compiled file will be found in the dist directory
 * in the root as main.min.js.
 */
import React from 'react';
import ReactDOM from 'react-dom';
import TemplateContext from './context-switching'
import '../scss/index.scss';

/**
 * Calculate render sequence when iframeScope is ready.
 * 
 * This removes the need to use setTimeout's anytime I use an iframeScope call.
 */
 var iframeScopeReady = setInterval(() => {

    // Only load when iframeScope is an object
    if ( typeof $scope === 'object' && typeof $scope.iframeScope === 'object' ) {

        // Let others know it's EE
        jQuery('body').addClass('context-switching-active');

        // Do Context Switching
        RunContextSwitching();

        // Remove this timer.
        clearInterval(iframeScopeReady);
    }

}, 100);


/**
 * Run Context Switching when called.
 */
function RunContextSwitching() {

    console.log('You are running the standalone edition of Context Switching. For even more power, consider upgrading to the Editor Enhancer suite, which includes Context Switching!');

    jQuery('.oxygen-toolbar-panels .oxygen-toolbar-panel:first-child').append('<div id="ee-template-context"></div>');

    /**
      * Context switching to update iframe body class????????
      */
    $scope.iframeScope.csBodyClass = ( isInner = false ) => {

        if ( isInner ) {

            document.body.classList.add('ct_inner');
        }
        
        else {
            
            document.body.classList.remove('ct_inner');
        }
    }

    ReactDOM.render(
        <React.StrictMode>
            <TemplateContext />
        </React.StrictMode>,
        document.getElementById('ee-template-context')
    )
}