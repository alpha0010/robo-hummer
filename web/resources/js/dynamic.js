var $ = window.jQuery;

// These seem to be hard-coded into reveal.js.
var revealHeight = 700;
var revealWidth = 960;

$(document).ready(function(){
    // TODO: Switch these automatically
    switchVerse('v1', 'data-v1')
    switchVerse('v2', 'data-v2')
    switchVerse('v3', 'data-v3')
    switchVerse('v4', 'data-v4')
    fillDynamicOptions();
    setDisplay('melody', 'all', false);
    autosetNotesPerLine();

    var audio = document.getElementById('audio')
    audio.onended = function() {
        if (window.Reveal.availableRoutes()['right']) {
            window.Reveal.right();
            this.play()
        }
    };
});

/**
 * @brief Set the notes per line automatically based on the breathSections.
 */
function autosetNotesPerLine() {
    var breathSections = $('.dynamic.original svg:first #breathSections rect');
    var maxNPL = 0;
    if (breathSections.length) {
        for (var i = 0; i < breathSections.length; i++) {
            var npl = parseInt(breathSections[i].attributes['data-width'].value);
            if (npl > maxNPL) {
                maxNPL = npl;
            }
        }
        if (maxNPL >= 1) {
            return setNotesPerLine(maxNPL);
        }
    }
    return setNotesPerLine(12);
}

function setDisplay(type, verse='all', doSetupPages = true) {
    if (type == 'lyrics') {
        hideParts(verse);
    } else if (type == 'melody') {
        hideParts(verse);
        var melodyColor = $('#parts rect[fill]:first-of-type').attr('fill');
        togglePart(verse, melodyColor);
    } else if (type == 'harmony') {
        hideParts(verse, 'show');
    }
    resizeSVGHeight();
    if (doSetupPages) {
        setupPages();
    }
}

/**
 * @brief Change the size of the font for the lyrics.
 */
function setFontSize(s) {
    fontPixelSize = undefined;
    $('.dynamic svg g text').each(function(){
        this.setAttribute('font-size', s + 'pt');
        this.setAttribute('dy', (-1 / 2) * s);
        squishText(this);
    });
    resizeSVGHeight();
}

/**
 * @brief Set the number of quarter notes to display per line.
 */
function setNotesPerLine(n) {
    if (n >= 1){
        setNoteWidth(revealWidth/n);
    }
}

/**
 * @brief Change the size of a notes.
 */
function setNoteHeight(h) {
    $('.dynamic svg rect[data-y]').each(function(){
        var y = parseFloat(this.attributes['data-y']['value']) * h;
        this.setAttribute('y', y);
        var height = parseFloat(this.attributes['data-height']['value']) * h;
        this.setAttribute('height', height);
    });
    resizeSVGHeight();
}

function setNoteWidth(w) {
    $('.dynamic svg [data-x]').each(function(){
        var x = parseFloat(this.attributes['data-x']['value']) * w;
        this.setAttribute('x', x);
        if (typeof this.attributes['data-width'] != 'undefined') {
            var width = parseFloat(this.attributes['data-width']['value']) * w;
            this.setAttribute('width', width);
        } else if (typeof this.attributes['data-tl'] != 'undefined') {
            var width = parseFloat(this.attributes['data-tl']['value']) * w;
            this.setAttribute('data-textlength', width);
            squishText(this);
        }
    });
    resizeSVGWidth();
}

/**
 * @brief Change the lyrics in a dynamic.svg to a different verse.
 * @param string id the html id surrounding the svgs that you want to change.
 * @param string verseAttr 'data-v#' where # is a verse number (see tools/dynamic.py).
 */
function switchVerse(id, verseAttr) {
    // TODO: Don't get rid of text that we need (show the chorus on verse two).
    $('#' + id + ' .dynamic svg g text').each(function(){this.innerHTML = "";});

    var els = $('#' + id + ' svg g text[' + verseAttr + ']');
    for (var i = 0; i < els.length; i++) {
        // Remove the hypens that start a syllable. We don't need two hypens per word.
        var text = $(els[i]).attr(verseAttr).replace(/^[ -]*/, "")
        // Add a non-breaking space if it is the end of a word.
        text = text.replace(/([^-])$/, "$1&nbsp;");
        els[i].innerHTML = text;
        squishText(els[i]);
    }
}

/**
 * @brief Show or hide dynamic options.
 */
function toggleDynamicOptions() {
    if ($('#dynamicOptions').hasClass('visible')) {
        $('#dynamicOptions').removeClass('visible');
    } else {
        $('#dynamicOptions').addClass('visible');
    }
}

/**
 * @brief Show or hide a part from a specific verse.
 * @param id The html id surrounding the svgs that you want to change.
 * @param partColor The fill color of the part that you want to hide.
 */
function togglePart(verse, partColor) {
    var selector = '.dynamic svg rect[fill="' + partColor + '"]';
    if (verse != 'all') {
        selector = '#v' + verse + ' ' + selector;
    }
    $(selector).each( function() {
        // TODO: Create CSS rule to toggle with less latency.
        $(this).toggle();
    });
}

window.autosetNotesPerLine = autosetNotesPerLine;
window.setDisplay = setDisplay;
window.setFontSize = setFontSize;
window.setNotesPerLine = setNotesPerLine;
window.setNoteHeight = setNoteHeight;
window.setNoteWidth = setNoteWidth;
window.switchVerse = switchVerse;
window.toggleDynamicOptions = toggleDynamicOptions;
window.togglePart = togglePart;

/**
 * @brief Count the number of verses in this dynamic presentation.
 */
function countVerses() {
    // Grab the first SVG
    var svgSelector = '.slides > section:first-of-type > section:first-of-type .dynamic svg'
    var i;
    for (i = 1; i < 10; i++) {
        var selector = svgSelector + " [data-v" + i + "]";
        if ($(selector).length == 0) {
            return i - 1;
        }
    }
    return i;
}

/**
 * @brief Fill the dynamic options with the controls for this song.
 */
function fillDynamicOptions() {
    var option = $("<div>");
    option.html("All Verses");
    $('#dynamicOptions .viewport-inner').append(option);
    getDisplaySetter('all', option);
    for ( var i = 1; i <= countVerses(); i++) {
        var option = $("<div>");
        $(option).addClass("v" + i);
        option.html("Verse " + i);
        $('#dynamicOptions .viewport-inner').append(option);
        getDisplaySetter(i, option);
    }
}

/**
 * @brief Return the current font size of the lyrics, or 0 if there are no lyrics.
 */
function getFontPixelSize() {
    // Use a global variable to improve speed.
    if (typeof fontPixelSize == "undefined") {
        var size = $('.dynamic svg text[data-v1]').attr('font-size');
        if (size == undefined) {
            fontPixelSize = 0;
        } else if (size.indexOf("pt") != -1) {
            fontPixelSize = parseFloat(size.replace("pt", "")) * (4/3)
        } else if (size.indexOf("px") != -1) {
            fontPixelSize = parseFloat(size.replace("px", ""))
        }
    }
    return fontPixelSize;
}

/**
 * @brief Return the current pixel value for the note height.
 */
function getNoteHeight() {
    var denominator = parseFloat($('.dynamic svg rect[data-height]').attr('data-height'));
    var numerator = parseInt($('.dynamic svg rect[data-height]').attr('height'));
    return numerator/denominator;
}
function getNoteWidth() {
    var denominator = parseFloat($('.dynamic svg rect[data-width]').attr('data-width'));
    var numerator = parseInt($('.dynamic svg rect[data-width]').attr('width'));
    return numerator/denominator;
}

/**
 * @brief Get the Number of notes tall a specific svg should be.
 */
function getSVGNoteRange(svg) {
    var parts = $(svg).find('#parts rect');
    var max = 0;
    for (var i = 0; i < parts.length; i++) {
        // togglePart sets display to none.
        if ($(parts[i]).css('display') != 'none') {
            var test = parseFloat($(parts[i]).attr('data-y'))
                + parseFloat($(parts[i]).attr('data-height'));
            if (test > max) {
                max = test;
            }
        }
    }
    return max;
}
function getSVGSongLength(svg) {
    return $(svg).attr('data-songlength');
}

/**
 * @brief Append HTML for a setting to change the view of different verses.
 */
function getDisplaySetter(verse, section) {
    var choices = ['lyrics', 'melody', 'harmony'];
    for (var i = 0; i < choices.length; i++) {
        var button = $("<button>");
        $(button).attr('value', choices[i]);
        $(button).attr('onclick', 'window.setDisplay(this.value, "' + verse + '");');
        $(button).html(choices[i]);
        $(section).append(button);
    }
}

/**
 * @brief get HTML for a toggle option to remove parts.
 */
function getPartsToggler(verse, section) {
    // Take the parts from the first SVG.
    var parts = $('.slides > section:first-of-type > section .dynamic.original svg #parts rect');
    for (var i = 0; i < parts.length; i++) {
        var fill = $(parts[i]).attr('fill');
        var button = $("<button>");
        button.html(fill);
        $(button).attr('onclick', 'window.togglePart("' + verse + '","' + fill + '")');
        $(button).css('background-color', fill);
        $(section).append(button);
    }
}

/**
 * @brief Hide all parts of music.
 * @param verse either 'all', or the verse number for which you want to hide parts.
 * @param method optionally "show" to show parts instead of hide them.
 */
function hideParts(verse, method='hide') {
    var selector = ' .dynamic svg rect[fill]'
    if (verse != 'all') {
        selector = '#v' + verse + selector;
    }
    $(selector).each( function() {
        // TODO: Create CSS rule to show/hide with less latency.
        $(this)[method]();
    });
}

/**
 * @brief Reset RevealJS so it detects the changes we've made to the DOM.
 */
function resetReveal() {
    var indices = window.Reveal.getIndices();
    window.Reveal.slide(indices.h, indices.v);
}

/**
 * @brief Resize all SVGs based on the current note height and font size.
 */
function resizeSVGHeight() {
    var nh = getNoteHeight();
    var fs = getFontPixelSize();
    $('svg').each(function(){
        var noteRange = getSVGNoteRange(this);
        var h = (noteRange * nh) + (fs * (4 / 3));
        this.setAttribute('height', h);
    });
    setViewBoxes();
    resetReveal();
}
/**
 * @brief Resize all SVGs based on the current note width.
 */
function resizeSVGWidth() {
    var nw = getNoteWidth();
    $('svg').each(function(){
        var songLength = getSVGSongLength(this);
        this.setAttribute('width', songLength * nw);
    });
    setupPages();
}

/**
 * @brief Create different pages for each verse. Fill them with multiple svg "lines".
 *  Note: This function is a little slow. We should only run it when needed.
 */
function setupPages() {
    var unoriginal = $('.dynamic:not(.original)');
    // Do this syncronously so we don't have a race condition for item.parentElement.children.length.
    for (var i = 0; i < unoriginal.length; i++) {
        var item = unoriginal[i];
        if (item.parentElement.children.length == 1) {
            item.parentElement.remove();
        } else {
            item.remove();
    }

    }
    $('.dynamic.original svg').each(function(){
        var slideGroup = $(this).closest('section.stack');
        var slide = $(this).closest('section');

        var numPages = 0;
        if ($(this).find('#breathSections rect').length > 1) {
            numPages = $(this).find('#breathSections rect').length;
        } else {
            numPages = Math.ceil($(this).attr('width') / revealWidth);
        }
        // Start at 1 because page 0 already exists.
        for (var i = 1; i < numPages; i++) {
            if (setupPageSlideIsFull(slide)) {
                slide = setupPageNewSlide(slide);
            }
            var child = $('<div>');
            $(child).addClass('dynamic');
            $(child).css('opacity', 0);
            $(child).attr('data-page', i);
            if (i % 2 == 1) {
                $(child).addClass('odd');
            }
            slide.append(child);
            $(slide).find('[data-page="' + i + '"]')[0].innerHTML = this.outerHTML;
        }
    });
    setViewBoxes();
    resetReveal();
}

/**
 * @brief Create and return a new slide after currentSlide.
 */
function setupPageNewSlide(currentSlide) {
    var slideGroup = $(currentSlide).closest('section.stack');
    slideGroup.append($("<section>"));
    return slideGroup.find('section:last-of-type');
}

/**
 * @brief return TRUE if slide cannot hold another svg "line" in it.
 */
function setupPageSlideIsFull(slide) {
    var svgHeight = parseInt($(slide).find('.dynamic svg').attr('height'));
    var canHold = Math.floor(revealHeight / svgHeight);
    var numberOfChildren = $(slide).find('.dynamic svg').length;
    return (numberOfChildren >= canHold);
}

/**
 * @brief Set the view boxes for each svg "line" so they start at the correct x value.
 */
function setViewBoxes() {
    var sections = $('.dynamic.original svg:first #breathSections rect');

    $('.dynamic svg').each(function(){
        var x = 0;
        var pageNum = $(this).closest('[data-page]').attr('data-page');
        if (sections.length > pageNum && $(sections[pageNum]).attr('width') > 0) {
            console.log(sections.length)
            x = sections[pageNum].attributes['x']['value'];
            $(this).attr('width', sections[pageNum].attributes['width']['value']);
        } else {
            x = pageNum * (revealWidth);
        }

        var height = $(this).attr('height');
        var width = $(this).attr('width');
        this.setAttribute('viewBox', x + ' 0 ' + width + ' ' + height);
    });
    $('.dynamic').css('opacity', 1);
}

/**
 * @brief Squish text so it doesn't go beyond the boundaries of its box.
 *  Also possibly removes hypens or adds non-breaking spaces to squished text elements.
 * @param el The element that you want to squish the text on.
 * @precondition The text in the element ends with a non-breaking space if it is the end of a word.
 */
function squishText(el) {
    // If there is no text here, we don't have to do anything.
    if (typeof el.childNodes[0] == "undefined") return;
    var text = el.childNodes[0].nodeValue;
    // Setting a specific letter width isn't perfect since "One" is wider than "ly,"
    var widthPerLetter = getFontPixelSize() * .7;
    var boxWidth = $(el).attr('data-textlength');

    // Add a hyphen if it doesn't end in a hypen or a non-breaking space.
    if (! text.match(/[\xA0-]$/)) {
        text += "-";
    }

    if (text.length * widthPerLetter >= boxWidth) {
        // Apply the textLength attribute if we need to squish these letters.
        $(el).attr('textLength', boxWidth);

        // If we need to squish this letter, it's okay to remove any trailing hyphens,
        // as long as removing those won't stretch the letter out.
        // (this syllable is the middle of a word, but is squished against its continuation)
        if ((text.length - 1) * widthPerLetter >= boxWidth) {
            text = text.replace(/-$/,"");
        }
    } else {
        $(el).attr('textLength', null);
    }
    el.innerHTML = text;
}
