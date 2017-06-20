/**
 * xedit JavaScript Library
 *
 * @author Shalganov Ivan < aco dot best at gmail dot com >
 * Dual licensed under the MIT and GPL licenses.
 * @version 1.0.3
 */
(function () {
	var undefined, window = this; // для увелечения скорости
	var xedit = null;
	
	
	window.xedit = xedit = {
	 
	insertTabChar: "\t", // символ табуляции
	doubleRow: true, // Возможность дублирования строк
	indentNewLine: true, // Отступ в новой строке равный отсуту предыдущей
	doubleExplode: true, // ctrl+enter
	
	eol: "\n",
	isIE: false,
	isOpera: false,
	isGecko: false,
	isChrome: false,
	regs: [],
	keeper: false,
	lineHeight: 20,
    
	/**
	 * Возвращает информацию о выделении. 
	 * 
	 * @param textarea - объект поля
	 * @returns object - start и end - позиции начала и конца выделения, если ничего не выделено то начало == конец == положению курсора в тексте (int),
	 * 					 scrollTop - текущая прокрутка в поле (int), selected - выделен ли текст (bool).
	 */
	getSelectionPos: function (textarea)
	{
		if(textarea.selectionStart !== undefined) {
			return {"start": textarea.selectionStart, "end": textarea.selectionEnd, "scrollTop": textarea.scrollTop, "selected": (textarea.selectionStart === textarea.selectionEnd) ? false : true};
		} else if(document.selection) {
			var range = document.selection.createRange();
			var dpl = range.duplicate();
			dpl.moveToElementText(textarea);
			dpl.setEndPoint("EndToEnd", range);
			if (range.text.length > 0)
			{
				return {"start": dpl.text.length - range.text.length, "end": dpl.text.length, "scrollTop": textarea.scrollTop, "selected": true};
			} else {
				return {"start": dpl.text.length - range.text.length, "end": dpl.text.length - range.text.length, "scrollTop": textarea.scrollTop, "selected:": false};
			}
		} else {
			return {"start":0, "end":0};
		}
	},
	
	/**
	 * Устанавливает выделене текста, а так же вертикальную прокрутку
	 * @param htmlObject textarea - объект поля
	 * @param int start - начало выделения (начало == конец == положение курсора в тексте)
	 * @param int end - конец выделения (начало == конец == положение курсора в тексте)
	 * @param [int scrollTop] - на сколько пикселей прокрутить
	 */
	setSelectionPos: function (textarea, start, end, scrollTop)
	{
		if (textarea.setSelectionRange !== undefined)
		{
			textarea.setSelectionRange(start, end);
		} else if (textarea.selectionStart !== undefined) {
			textarea.selectionStart = start;
			textarea.selectionEnd = end;
		} else if(textarea.createTextRange) {
			var selection = textarea.createTextRange();
			selection.collapse(true);
			var _fixB = textarea.value.substring(0, start).match(/\r/g);
			var fixB = _fixB ? _fixB.length : 0;
			var _fixI = textarea.value.substring(start, end).match(/\r/g);
			var fixI = _fixI ? _fixI.length : 0;
			selection.moveEnd("character", end - fixB - fixI);
			selection.moveStart("character", start - fixB );
			selection.select();
			
		} else {
			
		}
		if(scrollTop !== undefined)
			textarea.scrollTop = scrollTop;
	},
    
	/**
	 * Возвращает выделенный текст
	 * @param htmlObject textarea - объект поля
	 */
    getSelectText: function (textarea)
    {
		textarea.focus();
		if(textarea.selectionStart !== undefined) {
			return (textarea.value).substring(textarea.selectionStart, textarea.selectionEnd);
		} else if (document.selection) { 
			return document.selection.createRange().text;
		} else
			return false;
    },
    
	/**
	 * Заменяет выделенный текст
	 * @param htmlObject textarea - объект поля
	 * @param string text - текст на замену
	 * @param bool selectSelf - выделяет заменённый текст (bool)
	 */
    replaceSelectText: function (textarea, text, selectSelf)
    {
		selectSelf = !!selectSelf;
		var select = xedit.getSelectionPos(textarea);
		if(document.selection)
		{
			document.selection.createRange().text = text; 
		} else {
			textarea.value = textarea.value.replace(new RegExp("^((?:.|\\s){"+select.start+"})((?:.|\\s){"+(select.end-select.start)+"})"), "$1"+text);
			if(!selectSelf) {
				xedit.setSelectionPos(textarea, select.start + text.length,  select.start + text.length, select.scrollTop);
			}
		}
        
        if(selectSelf)
        {
			xedit.setSelectionPos(textarea, select.start,  select.start + text.length, select.scrollTop);
        }
		return true;
    },
    
	/**
	 * Имеет ли поле выделенный текст
	 * @param htmlObject textarea - объект поля
	 */
    hasSelectText: function (textarea)
    {
		var select = xedit.getSelectionPos(textarea);
		return select.selected;
    },
    
	/**
	 * Полностью выделяет строки, которые не полностью выделены
	 * @param htmlObject textarea - объект поля
	 */
    completeSelectLines: function (textarea)
    {
		var select = xedit.getSelectionPos(textarea);
		var newStart = select.start;
		var newEnd = select.end;
        do {
			if(!textarea.value.charAt(newStart-1))
				break;
            var ch = textarea.value.charAt(newStart-1);
            if(ch === "\n" || ch === "\r")
                break;
            newStart--;
        } while(1);
        
        do {
			if(!textarea.value.charAt(newEnd+1))
				break;
			var ch = textarea.value.charAt(newEnd);
            if(ch == "\n" || ch == "\r")
                break;
			newEnd++;
        } while(1);
		xedit.setSelectionPos(textarea, newStart, newEnd);
		return newStart;
    },
	
	/**
	 * Удаляет табуляцию слева от курсора
	 * @param htmlObject textarea - объект поля
	 */
	trimLeft: function (textarea)
	{
		var select = xedit.getSelectionPos(textarea);
		if(textarea.value.charAt(select.start-1) == "\t")
		{
			xedit.setSelectionPos(textarea, select.start - 1 , select.end);
			xedit.replaceSelectText(textarea, "", false);
		}
	},
	
	/**
	 * Триггер действий
	 * @param htmlObject textarea - объект поля
	 * @param eventObject e - объект event
	 * @param [function triggerSave] - функция, вызываемая при ctrl+s
	 */
    init: function (textarea, e, triggerSave)
    {
		var key = e.keyCode || e.which;
		
		if(e.ctrlKey)
		{
			if(e.altKey)
			{
				if(key === 38 && xedit.doubleRow) // ctrl + alt + up
					xedit.copyRowUp(textarea, true);
				if(key === 40 && xedit.doubleRow) // ctrl + alt + down
					xedit.copyRowDown(textarea, true);
				if(key == 97)
					xedit.selectRow(textarea);
			}
			if(triggerSave && ((xedit.isIE || xedit.isChrome) ? (key === 83 ? 1 : 0) : (key === 115 ? 1 : 0)))
			{
				xedit.cancelEvent(e);
				triggerSave(textarea);
			}
		} else {
			if(!e.altKey)
			{
				if(key == 9)
		        {
		            if(e.shiftKey) {
		                if(xedit.hasSelectText(textarea))
		                {
							xedit.completeSelectLines(textarea);
							var text = xedit.getSelectText(textarea);
							text = text.replace(xedit.regs[0], "$1");
							text = text.replace(/^(\t| {1,4})/, "");
							xedit.replaceSelectText(textarea, text, true);
						} else {
							xedit.trimLeft(textarea);
						}
		            } else {
		                if(xedit.hasSelectText(textarea))
		                {					
		                    xedit.completeSelectLines(textarea);
							var text = xedit.getSelectText(textarea);
		                    text = xedit.insertTabChar + text.replace(xedit.regs[1], "$1" + xedit.insertTabChar);
		                    xedit.replaceSelectText(textarea, text, true);
		                } else {
							xedit.replaceSelectText(textarea, xedit.insertTabChar, false);
		                }
		            }
					xedit.cancelEvent(e);
					textarea.focus();
					return false;
		        }
			}
		}
		if(key === 13 && xedit.indentNewLine)
		{
			var select = xedit.getSelectionPos(textarea);
			var start = xedit.completeSelectLines(textarea);
			var text = xedit.getSelectText(textarea);
			var textBegin = text.substring(0, select.start - start);
			var textEnd = text.substring(select.end - start, text.length);
			var scrollTop = textarea.scrollTop;
			var indent = textBegin.match(/^\s+/);
			if(!indent && !e.ctrlKey)
			{
				xedit.setSelectionPos(textarea, select.start, select.end);
				return true;
			}
			var indent = indent ? indent[0] : "";
			if(e.ctrlKey && xedit.doubleExplode && textEnd.length > 0)
				text = textBegin + xedit.eol + indent + xedit.eol + indent + textEnd;
			else
				text = textBegin + xedit.eol + indent + textEnd;
				
			xedit.replaceSelectText(textarea, text, false);
			var s = start + textBegin.length + xedit.eol.length + indent.length;
			xedit.setSelectionPos(textarea, s, s, scrollTop + xedit.lineHeight);
			xedit.cancelEvent(e);
			textarea.focus();
			return false;
		}
		return true;
    },
	
	/**
	 * "Вешается" на поле
	 * @param htmlObject | array elemnts - элемент или список элементов к которым приминить xedit
	 * @param [function saveFunc] - функция, вызываемая при ctrl+s
	 */
	bind: function (elemnts, saveFunc)
	{
		if(typeof elemnts !== "object")
			return;
		
		if(elemnts[0] !== undefined)
		{
			for(var i=0; i<elemnts.length; i++)
			{
				xedit.bind(elemnts[i], saveFunc);
			}
		} else {
			if (xedit.isIE || xedit.isChrome) xedit.event(elemnts, "keydown", function(event) {xedit.init(elemnts, event, saveFunc)});
			else xedit.event (elemnts, "keypress", function(event) {xedit.init(elemnts, event, saveFunc)});
		}
	},
	
	/**
	 * Вешает событие на объект
	 */
	event: function (object, event, handler, useCapture)
	{
		if (object.addEventListener) {
			object.addEventListener(event, handler, !!useCapture);
		} else if (object.attachEvent) {
			object.attachEvent('on' + event, handler);
		} else {
			object["on" + event] = handler;
		}
	},
	
	/**
	 * Отменяет событие
	 */
	cancelEvent: function (e)
	{
		if(e.cancelBubble !== undefined) e.cancelBubble = true;
		if(e.stopPropagation) e.stopPropagation();
		if(e.preventDefault) e.preventDefault();
		e.returnValue = false;
	},
	
	/**
	 * Устанавливает настройки
	 * @param object settings - может иметь следующие параметры:...
	 */
	setSettings: function (settings)
	{
		for(var key in settings)
		{
			if(xedit[key] !== undefined && typeof xedit[key] !== "function")
			{
				xedit[key] = settings[key];
			}
		}
	},
	
	/**
	 * Определяет основные константы
	 */
	define: function ()
	{
		var ua = navigator.userAgent.toLowerCase();
		xedit.isIE = (ua.indexOf("msie") != -1 && ua.indexOf("opera") == -1);
		xedit.isGecko = (ua.indexOf("gecko") != -1);
		xedit.isOpera = (ua.indexOf("opera") != -1);
		xedit.isChrome = (ua.indexOf("chrome") != -1);
		xedit.eol = (ua.indexOf("mac") != -1) ? "\r" : (ua.indexOf("windows") != -1) ? "\r\n" : "\n";
		if(xedit.isGecko) xedit.eol = "\n";
		xedit.regs = [new RegExp("("+xedit.eol+")(\t| {1,4})","g"), new RegExp("("+xedit.eol+")","g")];
		window.onbeforeunload = function () {
			var quest = null;
			if(typeof(xedit.keeper) === "function")
				quest = xedit.keeper();
			else
				quest = xedit.keeper;
			if(quest)
				return quest;
		};
		
		return true;
	},
	
	/************ UTILs *************/
	
	/**
	 * Копирует строку вверх
	 */
	copyRowUp: function (textarea, selectSelf)
	{
		selectSelf = !!selectSelf;
		var start = xedit.completeSelectLines(textarea);
		var text = xedit.getSelectText(textarea);
		var textLength = text.length;
		var scrollTop = textarea.scrollTop;
		text += xedit.eol + text;
		xedit.replaceSelectText(textarea, text, false);
		if(selectSelf)
			xedit.setSelectionPos(textarea, start , start + textLength, scrollTop - xedit.lineHeight);
	},
	
	/**
	 * Копирует строку вниз
	 */
	copyRowDown: function (textarea, selectSelf)
	{
		selectSelf = !!selectSelf;
		var start = xedit.completeSelectLines(textarea);
		var text = xedit.getSelectText(textarea);
		var textLength = text.length;
		var scrollTop = textarea.scrollTop;
		text += xedit.eol + text;
		xedit.replaceSelectText(textarea, text, false);
		if(selectSelf)
		{
			xedit.setSelectionPos(textarea, start + textLength + xedit.eol.length , start + textLength*2 + xedit.eol.length, scrollTop + xedit.lineHeight);
		}
	},
	
	selectRow: function (textarea)
	{
		xedit.completeSelectLines(textarea);
	},
	
	keepPage: function(keeper)
	{
		if(!keeper)
			xedit.keeper = false;
		else
			xedit.keeper = keeper;
	}
}})();

xedit.define();
