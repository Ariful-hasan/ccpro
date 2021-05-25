/**
 * BanglaInputManager jQuery Plugin for typing bangla into web pages.
 * This is the main engine and require any one or more of the following drivers
 * like phonetic, probhat, unijoy or inscript.
 *
 * @author: Hasin Hayder from Ekushey Team
 * @version: 0.11
 * @license: New BSD License
 * @date: 2010-03-08 [8th March, 2010]
 *
 * Contact at [hasin: countdraculla@gmail.com, manchu: manchumahara@gmail.com, omi: omi: omiazad@gmail.com]
 *
 * Changelog
 * Nov 21, 2010 - Fixed switch key browser incompatibility issue reported by Manchu and Sarim Khan.
 */
$.browser.chrome = /chrome/.test(navigator.userAgent.toLowerCase());
(function ($) {
    var opts;
    var common = 1;
    var switched = 0;
    var ctrlPressed;
    var lastInsertedString = "";
    var writingMode = "b";
    var carry;
    var switchKey = "y";

    $.fn.bnKb = function (options) {
        var defaults = {
            "switchkey": {
                "webkit": "k",
                "mozilla": "y",
                "safari": "k",
                "chrome": "k",
                "msie": "y"
            },
            "driver": phonetic,
            "writingMode": "b"
        };

        // Extend our default options with those provided.
        opts = $.extend(defaults, options);
        writingMode = opts.writingMode;
        carry = "";
        $(this).unbind("keypress keydown keyup");

        $(this).keyup($.fn.bnKb.ku);
        $(this).keydown($.fn.bnKb.kd);
        $(this).keypress($.fn.bnKb.kp);

        /* Browser Specific Switch Key fix - Thanks to Sarim Khan */
        if ($.browser.chrome) switchKey = opts.switchkey.chrome;
        else if ($.browser.safari || $.browser.safari) switchKey = opts.switchkey.webkit;
        else if ($.browser.msie) switchKey = opts.switchkey.msie;
        else if ($.browser.mozilla) switchKey = opts.switchkey.mozilla;

    }

    /**
     * handle keypress event
     * @param event ev
     */
    $.fn.bnKb.kp = function (ev) {
        console.log(ev);

        var keycode = ev.which;
        var keycode = ev.keyCode ? ev.keyCode : ev.which;
        var keystring = String.fromCharCode(keycode);
        //lets check if writing mode is english. if so, dont process anything
        if (writingMode == "e")
            return true;
        //end mode check


        if (ctrlPressed)
            $("#stat").html("Not Processing");
        else {
            var _carry = carry;
            carry += keystring;
            //processing intellisense
            if (opts.driver.supportIntellisense) {
                var mods = opts.driver.intellisense(keystring, _carry);
                if (mods) {
                    keystring = mods.keystring
                    carry = mods.carry;
                }
            }
            //end intellisense


            var replacement = opts.driver.keymaps[carry];
            if (replacement) {
                $.fn.bnKb.iac(this, replacement, 1);
                ev.stopPropagation();
                return false;
            }
            //carry processing end

            //if no equivalent is found for carry, then try it with relpacement itself
            replacement = opts.driver.keymaps[keystring];
            carry = keystring;
            if (replacement) {
                $.fn.bnKb.iac(this, replacement, 0);
                ev.stopPropagation();
                return false;
            }

            //nothing found, leave it as is
            lastInsertedString = "";
            return true;
        }
    }

    /**
     * handle keydown event
     * @param {event} ev
     */
    $.fn.bnKb.kd = function (ev) {
        var keycode = ev.keyCode ? ev.keyCode : ev.which;
        var keystring = String.fromCharCode(keycode).toLowerCase();
        if (keycode == 17 || keycode == 224 || keycode == 91) {
            ctrlPressed = true;
        }
        //lets check if user pressed the switchkey, then toggle the writing mode
        if (ctrlPressed && keystring == switchKey) {
            //console.log("Switching");
            (writingMode == "b") ? writingMode = "e" : writingMode = "b";
        }
        //end processing switchkey
    }

    /**
     * handle keyup event
     * @param event ev
     */
    $.fn.bnKb.ku = function (ev) {
        var keycode = ev.keyCode ? ev.keyCode : ev.which;
        if (keycode == 17 || keycode == 224 || keycode == 91) {
            ctrlPressed = false;
        }

    }


    /**
     * insert some string at current cursor position in a textarea or textbox
     * @param DOMElement obj
     * @param string input the string to insert in the textarea or textbox at cursor's current location
     * @param int length to shift
     * @param int type 0 for normal insertion, 1 for conjunct insertion
     */
    $.fn.bnKb.iac = function (obj, input, type) {
        var myField = obj;
        var myValue = input;

        len = lastInsertedString.length;
        if (!type)
            len = 0;
        if (document.selection) {
            myField.focus();
            sel = document.selection.createRange();
            if (myField.value.length >= len) { // here is that first conjunction bug in IE, if you use the > operator
                sel.moveStart('character', -1 * (len));
            }
            sel.text = myValue;
            sel.collapse(true);
            sel.select();
        }
        //MOZILLA/NETSCAPE support
        else {
            if (myField.selectionStart || myField.selectionStart == 0) {
                myField.focus();
                var startPos = myField.selectionStart - len;
                var endPos = myField.selectionEnd;
                var scrollTop = myField.scrollTop;
                startPos = (startPos == -1 ? myField.value.length : startPos);
                myField.value = myField.value.substring(0, startPos) +
                    myValue +
                    myField.value.substring(endPos, myField.value.length);
                myField.focus();
                myField.selectionStart = startPos + myValue.length;
                myField.selectionEnd = startPos + myValue.length;
                myField.scrollTop = scrollTop;
            }
            else {
                var scrollTop = myField.scrollTop;
                myField.value += myValue;
                if (myField.tagName === "DIV") {
                    myField.focus();
                    $.fn.bnKb.writeInDiv(myValue, myField, type);

                }
                myField.focus();
                myField.scrollTop = scrollTop;
            }
        }
        lastInsertedString = myValue;
    }

    // write in content editable div

    $.fn.bnKb.writeInDiv = function (myValue, myField, type) {
        var sel, range;

        var karAndFola = ['\u09BF', '\u09C0', '\u09C7', '\u09C1', '\u09C2',
            '\u09BE', '\u0983', '\u09CD\u200C', '\u0981', '\u09CD\u09AF',
            '\u09CD\u09AC', '\u09C3', '\u09CD', '\u09CB', '\u09C8', "\u09CC",
            '\u0982', '\u0983'
        ];

        var juktoKar = ['\u09C0', '\u09C2', '\u09C8', '\u09CC'];

        var isChrome = !!window.chrome && !!window.chrome.webstore;
        if (window.getSelection) {
            // IE9 and non-IE
            sel = window.getSelection();

            if (type && myField.innerText.length !== 0) {
                var tmp = $.fn.bnKb.caretPosition(myField, karAndFola);

                var tmptext = $.fn.bnKb.caretPositionString(myField);
                var text = myField.innerText;
                var substrlen = tmptext.length;

                myField.innerText = text.substring(0, substrlen - 1) + text.substring(substrlen, text.length);
                var caretShift = tmp - 1;
                if (juktoKar.indexOf(myValue) !== -1) {
                    caretShift = tmp;
                }

                for (let position = 0; position < caretShift; position++) {
                    sel.modify("move", "right", "character"); //move the cursor to right direction
                }
            }

            if (sel.getRangeAt && sel.rangeCount) {
                range = sel.getRangeAt(0);
                range.deleteContents();
                // Range.createContextualFragment() would be useful here but is
                // non-standard and not supported in all browsers (IE9, for one)
                var el = document.createElement("div");
                el.innerHTML = myValue;
                var frag = document.createDocumentFragment(), node, lastNode;
                while ((node = el.firstChild)) {
                    lastNode = frag.appendChild(node);
                }
                range.insertNode(frag);
//                var cr = sel.anchorOffset;
                if (lastNode) {
                    range = range.cloneRange();
                    range.setStartAfter(lastNode);
                    range.collapse(true);
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
                if (karAndFola.indexOf(myValue) !== -1 && isChrome) {
                    var range_temp = window.getSelection().getRangeAt(0),
                        preCaretRange = range.cloneRange(),
                        caretPosition,
                        tmp = document.createElement("div");

                    preCaretRange.selectNodeContents(myField);
                    preCaretRange.setEnd(range_temp.endContainer, range_temp.endOffset);
                    tmp.appendChild(preCaretRange.cloneContents());
                    caretPosition = $.fn.bnKb.caretPosition(myField, karAndFola);

                    var msg = myField.innerHTML;
                    myField.innerHTML = null;
                    myField.innerHTML = msg;
                    for (let position = 0; position < caretPosition; position++) {
                        sel.modify("move", "right", "character"); //move the cursor to right direction
                    }

                }

            }

        } else if (document.selection && document.selection.type !== "Control") {
            // IE < 9
            document.selection.createRange().pasteHTML(myValue);
        }
    };

    $.fn.bnKb.caretPosition = function getCaretPosition(node, charList) {
        var range = window.getSelection().getRangeAt(0),
            preCaretRange = range.cloneRange(),
            caretPosition,
            tmp = document.createElement("div");

        preCaretRange.selectNodeContents(node);
        preCaretRange.setEnd(range.endContainer, range.endOffset);
        tmp.appendChild(preCaretRange.cloneContents());
        caretPosition = 0;
        for (var i = 0; i <= tmp.innerText.length; i++) {
            var chr = tmp.innerText.charAt(i);
            if (charList.indexOf(chr) === -1) {
                if (lastInsertedString === '\u09CD')
                    continue;
                caretPosition++;
            }
        }
        var isChrome = !!window.chrome && !!window.chrome.webstore;
        if (caretPosition > 0) {
            caretPosition = caretPosition - 1;
            if (isChrome && lastInsertedString === '\u09CD') {
                caretPosition = caretPosition - 1;
            }
        }
        if (!isChrome) {
            var lineCount = (tmp.innerHTML.match(/<\/div>/g));

            if (lineCount != null) {
                lineCount = Object.keys(lineCount).length;
                if (lineCount > 0 ){
                    caretPosition = caretPosition + (lineCount-1);
                }
            }
        }
        return caretPosition;
    }

    $.fn.bnKb.caretPositionString = function getCaretPositionString(node) {
        var range = window.getSelection().getRangeAt(0),
            preCaretRange = range.cloneRange(),
            tmp = document.createElement("div");

        preCaretRange.selectNodeContents(node);
        preCaretRange.setEnd(range.endContainer, range.endOffset);
        tmp.appendChild(preCaretRange.cloneContents());

        return tmp.innerText;
    }


    /**
     * handle language type
     * @param set language value bangla/english
     */
    $.fn.bnKb.lang_change = function (lan) {
        writingMode = lan;
    }

})(jQuery);
