/*!
 * Bootstrap v4.4.1 (https://getbootstrap.com/)
 * Copyright 2011-2019 The Bootstrap Authors (https://github.com/twbs/bootstrap/graphs/contributors)
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 */
!(function (t, e) {
    "object" == typeof exports && "undefined" != typeof module
        ? e(exports, require("jquery"), require("popper.js"))
        : "function" == typeof define && define.amd
        ? define(["exports", "jquery", "popper.js"], e)
        : e(((t = t || self).bootstrap = {}), t.jQuery, t.Popper);
})(this, function (t, e, n) {
    "use strict";
    function i(t, e) {
        for (var n = 0; n < e.length; n++) {
            var i = e[n];
            (i.enumerable = i.enumerable || !1), (i.configurable = !0), "value" in i && (i.writable = !0), Object.defineProperty(t, i.key, i);
        }
    }
    function r(t, e, n) {
        return e && i(t.prototype, e), n && i(t, n), t;
    }
    function s(t, e, n) {
        return e in t ? Object.defineProperty(t, e, { value: n, enumerable: !0, configurable: !0, writable: !0 }) : (t[e] = n), t;
    }
    function o(t, e) {
        var n = Object.keys(t);
        if (Object.getOwnPropertySymbols) {
            var i = Object.getOwnPropertySymbols(t);
            e &&
                (i = i.filter(function (e) {
                    return Object.getOwnPropertyDescriptor(t, e).enumerable;
                })),
                n.push.apply(n, i);
        }
        return n;
    }
    function a(t) {
        for (var e = 1; e < arguments.length; e++) {
            var n = null != arguments[e] ? arguments[e] : {};
            e % 2
                ? o(Object(n), !0).forEach(function (e) {
                      s(t, e, n[e]);
                  })
                : Object.getOwnPropertyDescriptors
                ? Object.defineProperties(t, Object.getOwnPropertyDescriptors(n))
                : o(Object(n)).forEach(function (e) {
                      Object.defineProperty(t, e, Object.getOwnPropertyDescriptor(n, e));
                  });
        }
        return t;
    }
    (e = e && e.hasOwnProperty("default") ? e.default : e), (n = n && n.hasOwnProperty("default") ? n.default : n);
    var l = "transitionend";
    function c(t) {
        return {}.toString
            .call(t)
            .match(/\s([a-z]+)/i)[1]
            .toLowerCase();
    }
    var h = {
        TRANSITION_END: "bsTransitionEnd",
        getUID: function t(e) {
            do e += ~~(1e6 * Math.random());
            while (document.getElementById(e));
            return e;
        },
        getSelectorFromElement: function t(e) {
            var n = e.getAttribute("data-target");
            if (!n || "#" === n) {
                var i = e.getAttribute("href");
                n = i && "#" !== i ? i.trim() : "";
            }
            try {
                return document.querySelector(n) ? n : null;
            } catch (r) {
                return null;
            }
        },
        getTransitionDurationFromElement: function t(n) {
            if (!n) return 0;
            var i = e(n).css("transition-duration"),
                r = e(n).css("transition-delay"),
                s = parseFloat(i),
                o = parseFloat(r);
            return s || o ? ((i = i.split(",")[0]), (r = r.split(",")[0]), (parseFloat(i) + parseFloat(r)) * 1e3) : 0;
        },
        reflow: function t(e) {
            return e.offsetHeight;
        },
        triggerTransitionEnd: function t(n) {
            e(n).trigger(l);
        },
        supportsTransitionEnd: function t() {
            return Boolean(l);
        },
        isElement: function t(e) {
            return (e[0] || e).nodeType;
        },
        typeCheckConfig: function t(e, n, i) {
            for (var r in i)
                if (Object.prototype.hasOwnProperty.call(i, r)) {
                    var s = i[r],
                        o = n[r],
                        a = o && h.isElement(o) ? "element" : c(o);
                    if (!RegExp(s).test(a)) throw Error(e.toUpperCase() + ": " + ('Option "' + r + '" provided type "') + a + '" but expected type "' + s + '".');
                }
        },
        findShadowRoot: function t(e) {
            if (!document.documentElement.attachShadow) return null;
            if ("function" == typeof e.getRootNode) {
                var n = e.getRootNode();
                return n instanceof ShadowRoot ? n : null;
            }
            return e instanceof ShadowRoot ? e : e.parentNode ? h.findShadowRoot(e.parentNode) : null;
        },
        jQueryDetection: function t() {
            if (void 0 === e) throw TypeError("Bootstrap's JavaScript requires jQuery. jQuery must be included before Bootstrap's JavaScript.");
            var n = e.fn.jquery.split(" ")[0].split(".");
            if ((n[0] < 2 && n[1] < 9) || (1 === n[0] && 9 === n[1] && n[2] < 1) || n[0] >= 4) throw Error("Bootstrap's JavaScript requires at least jQuery v1.9.1 but less than v4.0.0");
        },
    };
    h.jQueryDetection(),
        (e.fn.emulateTransitionEnd = function t(n) {
            var i = this,
                r = !1;
            return (
                e(this).one(h.TRANSITION_END, function () {
                    r = !0;
                }),
                setTimeout(function () {
                    r || h.triggerTransitionEnd(i);
                }, n),
                this
            );
        }),
        (e.event.special[h.TRANSITION_END] = {
            bindType: l,
            delegateType: l,
            handle: function t(n) {
                if (e(n.target).is(this)) return n.handleObj.handler.apply(this, arguments);
            },
        });
    var u = "alert",
        f = "bs.alert",
        d = "." + f,
        g = e.fn[u],
        m = { CLOSE: "close" + d, CLOSED: "closed" + d, CLICK_DATA_API: "click" + d + ".data-api" },
        p = { ALERT: "alert", FADE: "fade", SHOW: "show" },
        E = (function () {
            function t(t) {
                this._element = t;
            }
            var n = t.prototype;
            return (
                (n.close = function t(e) {
                    var n = this._element;
                    e && (n = this._getRootElement(e)), !this._triggerCloseEvent(n).isDefaultPrevented() && this._removeElement(n);
                }),
                (n.dispose = function t() {
                    e.removeData(this._element, f), (this._element = null);
                }),
                (n._getRootElement = function t(n) {
                    var i = h.getSelectorFromElement(n),
                        r = !1;
                    return i && (r = document.querySelector(i)), r || (r = e(n).closest("." + p.ALERT)[0]), r;
                }),
                (n._triggerCloseEvent = function t(n) {
                    var i = e.Event(m.CLOSE);
                    return e(n).trigger(i), i;
                }),
                (n._removeElement = function t(n) {
                    var i = this;
                    if ((e(n).removeClass(p.SHOW), !e(n).hasClass(p.FADE))) {
                        this._destroyElement(n);
                        return;
                    }
                    var r = h.getTransitionDurationFromElement(n);
                    e(n)
                        .one(h.TRANSITION_END, function (t) {
                            return i._destroyElement(n, t);
                        })
                        .emulateTransitionEnd(r);
                }),
                (n._destroyElement = function t(n) {
                    e(n).detach().trigger(m.CLOSED).remove();
                }),
                (t._jQueryInterface = function n(i) {
                    return this.each(function () {
                        var n = e(this),
                            r = n.data(f);
                        r || ((r = new t(this)), n.data(f, r)), "close" === i && r[i](this);
                    });
                }),
                (t._handleDismiss = function t(e) {
                    return function (t) {
                        t && t.preventDefault(), e.close(this);
                    };
                }),
                r(t, null, [
                    {
                        key: "VERSION",
                        get: function t() {
                            return "4.4.1";
                        },
                    },
                ]),
                t
            );
        })();
    e(document).on(m.CLICK_DATA_API, '[data-dismiss="alert"]', E._handleDismiss(new E())),
        (e.fn[u] = E._jQueryInterface),
        (e.fn[u].Constructor = E),
        (e.fn[u].noConflict = function () {
            return (e.fn[u] = g), E._jQueryInterface;
        });
    var v = "button",
        T = "bs.button",
        A = "." + T,
        I = ".data-api",
        S = e.fn[v],
        C = { ACTIVE: "active", BUTTON: "btn", FOCUS: "focus" },
        D = {
            DATA_TOGGLE_CARROT: '[data-toggle^="button"]',
            DATA_TOGGLES: '[data-toggle="buttons"]',
            DATA_TOGGLE: '[data-toggle="button"]',
            DATA_TOGGLES_BUTTONS: '[data-toggle="buttons"] .btn',
            INPUT: 'input:not([type="hidden"])',
            ACTIVE: ".active",
            BUTTON: ".btn",
        },
        O = { CLICK_DATA_API: "click" + A + I, FOCUS_BLUR_DATA_API: "focus" + A + I + " blur" + A + I, LOAD_DATA_API: "load" + A + I },
        y = (function () {
            function t(t) {
                this._element = t;
            }
            var n = t.prototype;
            return (
                (n.toggle = function t() {
                    var n = !0,
                        i = !0,
                        r = e(this._element).closest(D.DATA_TOGGLES)[0];
                    if (r) {
                        var s = this._element.querySelector(D.INPUT);
                        if (s) {
                            if ("radio" === s.type) {
                                if (s.checked && this._element.classList.contains(C.ACTIVE)) n = !1;
                                else {
                                    var o = r.querySelector(D.ACTIVE);
                                    o && e(o).removeClass(C.ACTIVE);
                                }
                            } else "checkbox" === s.type ? "LABEL" === this._element.tagName && s.checked === this._element.classList.contains(C.ACTIVE) && (n = !1) : (n = !1);
                            n && ((s.checked = !this._element.classList.contains(C.ACTIVE)), e(s).trigger("change")), s.focus(), (i = !1);
                        }
                    }
                    !(this._element.hasAttribute("disabled") || this._element.classList.contains("disabled")) &&
                        (i && this._element.setAttribute("aria-pressed", !this._element.classList.contains(C.ACTIVE)), n && e(this._element).toggleClass(C.ACTIVE));
                }),
                (n.dispose = function t() {
                    e.removeData(this._element, T), (this._element = null);
                }),
                (t._jQueryInterface = function n(i) {
                    return this.each(function () {
                        var n = e(this).data(T);
                        n || ((n = new t(this)), e(this).data(T, n)), "toggle" === i && n[i]();
                    });
                }),
                r(t, null, [
                    {
                        key: "VERSION",
                        get: function t() {
                            return "4.4.1";
                        },
                    },
                ]),
                t
            );
        })();
    e(document)
        .on(O.CLICK_DATA_API, D.DATA_TOGGLE_CARROT, function (t) {
            var n = t.target;
            if ((e(n).hasClass(C.BUTTON) || (n = e(n).closest(D.BUTTON)[0]), !n || n.hasAttribute("disabled") || n.classList.contains("disabled"))) t.preventDefault();
            else {
                var i = n.querySelector(D.INPUT);
                if (i && (i.hasAttribute("disabled") || i.classList.contains("disabled"))) {
                    t.preventDefault();
                    return;
                }
                y._jQueryInterface.call(e(n), "toggle");
            }
        })
        .on(O.FOCUS_BLUR_DATA_API, D.DATA_TOGGLE_CARROT, function (t) {
            var n = e(t.target).closest(D.BUTTON)[0];
            e(n).toggleClass(C.FOCUS, /^focus(in)?$/.test(t.type));
        }),
        e(window).on(O.LOAD_DATA_API, function () {
            for (var t = [].slice.call(document.querySelectorAll(D.DATA_TOGGLES_BUTTONS)), e = 0, n = t.length; e < n; e++) {
                var i = t[e],
                    r = i.querySelector(D.INPUT);
                r.checked || r.hasAttribute("checked") ? i.classList.add(C.ACTIVE) : i.classList.remove(C.ACTIVE);
            }
            t = [].slice.call(document.querySelectorAll(D.DATA_TOGGLE));
            for (var s = 0, o = t.length; s < o; s++) {
                var a = t[s];
                "true" === a.getAttribute("aria-pressed") ? a.classList.add(C.ACTIVE) : a.classList.remove(C.ACTIVE);
            }
        }),
        (e.fn[v] = y._jQueryInterface),
        (e.fn[v].Constructor = y),
        (e.fn[v].noConflict = function () {
            return (e.fn[v] = S), y._jQueryInterface;
        });
    var N = "carousel",
        b = "bs.carousel",
        L = "." + b,
        w = ".data-api",
        P = e.fn[N],
        H = { interval: 5e3, keyboard: !0, slide: !1, pause: "hover", wrap: !0, touch: !0 },
        R = { interval: "(number|boolean)", keyboard: "boolean", slide: "(boolean|string)", pause: "(string|boolean)", wrap: "boolean", touch: "boolean" },
        $ = { NEXT: "next", PREV: "prev", LEFT: "left", RIGHT: "right" },
        k = {
            SLIDE: "slide" + L,
            SLID: "slid" + L,
            KEYDOWN: "keydown" + L,
            MOUSEENTER: "mouseenter" + L,
            MOUSELEAVE: "mouseleave" + L,
            TOUCHSTART: "touchstart" + L,
            TOUCHMOVE: "touchmove" + L,
            TOUCHEND: "touchend" + L,
            POINTERDOWN: "pointerdown" + L,
            POINTERUP: "pointerup" + L,
            DRAG_START: "dragstart" + L,
            LOAD_DATA_API: "load" + L + w,
            CLICK_DATA_API: "click" + L + w,
        },
        W = { CAROUSEL: "carousel", ACTIVE: "active", SLIDE: "slide", RIGHT: "carousel-item-right", LEFT: "carousel-item-left", NEXT: "carousel-item-next", PREV: "carousel-item-prev", ITEM: "carousel-item", POINTER_EVENT: "pointer-event" },
        V = {
            ACTIVE: ".active",
            ACTIVE_ITEM: ".active.carousel-item",
            ITEM: ".carousel-item",
            ITEM_IMG: ".carousel-item img",
            NEXT_PREV: ".carousel-item-next, .carousel-item-prev",
            INDICATORS: ".carousel-indicators",
            DATA_SLIDE: "[data-slide], [data-slide-to]",
            DATA_RIDE: '[data-ride="carousel"]',
        },
        U = { TOUCH: "touch", PEN: "pen" },
        F = (function () {
            function t(t, e) {
                (this._items = null),
                    (this._interval = null),
                    (this._activeElement = null),
                    (this._isPaused = !1),
                    (this._isSliding = !1),
                    (this.touchTimeout = null),
                    (this.touchStartX = 0),
                    (this.touchDeltaX = 0),
                    (this._config = this._getConfig(e)),
                    (this._element = t),
                    (this._indicatorsElement = this._element.querySelector(V.INDICATORS)),
                    (this._touchSupported = "ontouchstart" in document.documentElement || navigator.maxTouchPoints > 0),
                    (this._pointerEvent = Boolean(window.PointerEvent || window.MSPointerEvent)),
                    this._addEventListeners();
            }
            var n = t.prototype;
            return (
                (n.next = function t() {
                    this._isSliding || this._slide($.NEXT);
                }),
                (n.nextWhenVisible = function t() {
                    !document.hidden && e(this._element).is(":visible") && "hidden" !== e(this._element).css("visibility") && this.next();
                }),
                (n.prev = function t() {
                    this._isSliding || this._slide($.PREV);
                }),
                (n.pause = function t(e) {
                    e || (this._isPaused = !0), this._element.querySelector(V.NEXT_PREV) && (h.triggerTransitionEnd(this._element), this.cycle(!0)), clearInterval(this._interval), (this._interval = null);
                }),
                (n.cycle = function t(e) {
                    e || (this._isPaused = !1),
                        this._interval && (clearInterval(this._interval), (this._interval = null)),
                        this._config.interval && !this._isPaused && (this._interval = setInterval((document.visibilityState ? this.nextWhenVisible : this.next).bind(this), this._config.interval));
                }),
                (n.to = function t(n) {
                    var i = this;
                    this._activeElement = this._element.querySelector(V.ACTIVE_ITEM);
                    var r = this._getItemIndex(this._activeElement);
                    if (!(n > this._items.length - 1) && !(n < 0)) {
                        if (this._isSliding) {
                            e(this._element).one(k.SLID, function () {
                                return i.to(n);
                            });
                            return;
                        }
                        if (r === n) {
                            this.pause(), this.cycle();
                            return;
                        }
                        var s = n > r ? $.NEXT : $.PREV;
                        this._slide(s, this._items[n]);
                    }
                }),
                (n.dispose = function t() {
                    e(this._element).off(L),
                        e.removeData(this._element, b),
                        (this._items = null),
                        (this._config = null),
                        (this._element = null),
                        (this._interval = null),
                        (this._isPaused = null),
                        (this._isSliding = null),
                        (this._activeElement = null),
                        (this._indicatorsElement = null);
                }),
                (n._getConfig = function t(e) {
                    return (e = a({}, H, {}, e)), h.typeCheckConfig(N, e, R), e;
                }),
                (n._handleSwipe = function t() {
                    var e = Math.abs(this.touchDeltaX);
                    if (!(e <= 40)) {
                        var n = e / this.touchDeltaX;
                        (this.touchDeltaX = 0), n > 0 && this.prev(), n < 0 && this.next();
                    }
                }),
                (n._addEventListeners = function t() {
                    var n = this;
                    this._config.keyboard &&
                        e(this._element).on(k.KEYDOWN, function (t) {
                            return n._keydown(t);
                        }),
                        "hover" === this._config.pause &&
                            e(this._element)
                                .on(k.MOUSEENTER, function (t) {
                                    return n.pause(t);
                                })
                                .on(k.MOUSELEAVE, function (t) {
                                    return n.cycle(t);
                                }),
                        this._config.touch && this._addTouchEventListeners();
                }),
                (n._addTouchEventListeners = function t() {
                    var n = this;
                    if (this._touchSupported) {
                        var i = function t(e) {
                                n._pointerEvent && U[e.originalEvent.pointerType.toUpperCase()] ? (n.touchStartX = e.originalEvent.clientX) : n._pointerEvent || (n.touchStartX = e.originalEvent.touches[0].clientX);
                            },
                            r = function t(e) {
                                e.originalEvent.touches && e.originalEvent.touches.length > 1 ? (n.touchDeltaX = 0) : (n.touchDeltaX = e.originalEvent.touches[0].clientX - n.touchStartX);
                            },
                            s = function t(e) {
                                n._pointerEvent && U[e.originalEvent.pointerType.toUpperCase()] && (n.touchDeltaX = e.originalEvent.clientX - n.touchStartX),
                                    n._handleSwipe(),
                                    "hover" === n._config.pause &&
                                        (n.pause(),
                                        n.touchTimeout && clearTimeout(n.touchTimeout),
                                        (n.touchTimeout = setTimeout(function (t) {
                                            return n.cycle(t);
                                        }, 500 + n._config.interval)));
                            };
                        e(this._element.querySelectorAll(V.ITEM_IMG)).on(k.DRAG_START, function (t) {
                            return t.preventDefault();
                        }),
                            this._pointerEvent
                                ? (e(this._element).on(k.POINTERDOWN, function (t) {
                                      return i(t);
                                  }),
                                  e(this._element).on(k.POINTERUP, function (t) {
                                      return s(t);
                                  }),
                                  this._element.classList.add(W.POINTER_EVENT))
                                : (e(this._element).on(k.TOUCHSTART, function (t) {
                                      return i(t);
                                  }),
                                  e(this._element).on(k.TOUCHMOVE, function (t) {
                                      return r(t);
                                  }),
                                  e(this._element).on(k.TOUCHEND, function (t) {
                                      return s(t);
                                  }));
                    }
                }),
                (n._keydown = function t(e) {
                    if (!/input|textarea/i.test(e.target.tagName))
                        switch (e.which) {
                            case 37:
                                e.preventDefault(), this.prev();
                                break;
                            case 39:
                                e.preventDefault(), this.next();
                        }
                }),
                (n._getItemIndex = function t(e) {
                    return (this._items = e && e.parentNode ? [].slice.call(e.parentNode.querySelectorAll(V.ITEM)) : []), this._items.indexOf(e);
                }),
                (n._getItemByDirection = function t(e, n) {
                    var i = e === $.NEXT,
                        r = e === $.PREV,
                        s = this._getItemIndex(n),
                        o = this._items.length - 1;
                    if (((r && 0 === s) || (i && s === o)) && !this._config.wrap) return n;
                    var a = (s + (e === $.PREV ? -1 : 1)) % this._items.length;
                    return -1 === a ? this._items[this._items.length - 1] : this._items[a];
                }),
                (n._triggerSlideEvent = function t(n, i) {
                    var r = this._getItemIndex(n),
                        s = this._getItemIndex(this._element.querySelector(V.ACTIVE_ITEM)),
                        o = e.Event(k.SLIDE, { relatedTarget: n, direction: i, from: s, to: r });
                    return e(this._element).trigger(o), o;
                }),
                (n._setActiveIndicatorElement = function t(n) {
                    if (this._indicatorsElement) {
                        e([].slice.call(this._indicatorsElement.querySelectorAll(V.ACTIVE))).removeClass(W.ACTIVE);
                        var i = this._indicatorsElement.children[this._getItemIndex(n)];
                        i && e(i).addClass(W.ACTIVE);
                    }
                }),
                (n._slide = function t(n, i) {
                    var r,
                        s,
                        o,
                        a = this,
                        l = this._element.querySelector(V.ACTIVE_ITEM),
                        c = this._getItemIndex(l),
                        u = i || (l && this._getItemByDirection(n, l)),
                        f = this._getItemIndex(u),
                        d = Boolean(this._interval);
                    if ((n === $.NEXT ? ((r = W.LEFT), (s = W.NEXT), (o = $.LEFT)) : ((r = W.RIGHT), (s = W.PREV), (o = $.RIGHT)), u && e(u).hasClass(W.ACTIVE))) {
                        this._isSliding = !1;
                        return;
                    }
                    if (!this._triggerSlideEvent(u, o).isDefaultPrevented() && l && u) {
                        (this._isSliding = !0), d && this.pause(), this._setActiveIndicatorElement(u);
                        var g = e.Event(k.SLID, { relatedTarget: u, direction: o, from: c, to: f });
                        if (e(this._element).hasClass(W.SLIDE)) {
                            e(u).addClass(s), h.reflow(u), e(l).addClass(r), e(u).addClass(r);
                            var m = parseInt(u.getAttribute("data-interval"), 10);
                            m ? ((this._config.defaultInterval = this._config.defaultInterval || this._config.interval), (this._config.interval = m)) : (this._config.interval = this._config.defaultInterval || this._config.interval);
                            var p = h.getTransitionDurationFromElement(l);
                            e(l)
                                .one(h.TRANSITION_END, function () {
                                    e(u)
                                        .removeClass(r + " " + s)
                                        .addClass(W.ACTIVE),
                                        e(l).removeClass(W.ACTIVE + " " + s + " " + r),
                                        (a._isSliding = !1),
                                        setTimeout(function () {
                                            return e(a._element).trigger(g);
                                        }, 0);
                                })
                                .emulateTransitionEnd(p);
                        } else e(l).removeClass(W.ACTIVE), e(u).addClass(W.ACTIVE), (this._isSliding = !1), e(this._element).trigger(g);
                        d && this.cycle();
                    }
                }),
                (t._jQueryInterface = function n(i) {
                    return this.each(function () {
                        var n = e(this).data(b),
                            r = a({}, H, {}, e(this).data());
                        "object" == typeof i && (r = a({}, r, {}, i));
                        var s = "string" == typeof i ? i : r.slide;
                        if ((n || ((n = new t(this, r)), e(this).data(b, n)), "number" == typeof i)) n.to(i);
                        else if ("string" == typeof s) {
                            if (void 0 === n[s]) throw TypeError('No method named "' + s + '"');
                            n[s]();
                        } else r.interval && r.ride && (n.pause(), n.cycle());
                    });
                }),
                (t._dataApiClickHandler = function n(i) {
                    var r = h.getSelectorFromElement(this);
                    if (r) {
                        var s = e(r)[0];
                        if (s && e(s).hasClass(W.CAROUSEL)) {
                            var o = a({}, e(s).data(), {}, e(this).data()),
                                l = this.getAttribute("data-slide-to");
                            l && (o.interval = !1), t._jQueryInterface.call(e(s), o), l && e(s).data(b).to(l), i.preventDefault();
                        }
                    }
                }),
                r(t, null, [
                    {
                        key: "VERSION",
                        get: function t() {
                            return "4.4.1";
                        },
                    },
                    {
                        key: "Default",
                        get: function t() {
                            return H;
                        },
                    },
                ]),
                t
            );
        })();
    e(document).on(k.CLICK_DATA_API, V.DATA_SLIDE, F._dataApiClickHandler),
        e(window).on(k.LOAD_DATA_API, function () {
            for (var t = [].slice.call(document.querySelectorAll(V.DATA_RIDE)), n = 0, i = t.length; n < i; n++) {
                var r = e(t[n]);
                F._jQueryInterface.call(r, r.data());
            }
        }),
        (e.fn[N] = F._jQueryInterface),
        (e.fn[N].Constructor = F),
        (e.fn[N].noConflict = function () {
            return (e.fn[N] = P), F._jQueryInterface;
        });
    var M = "collapse",
        j = "bs.collapse",
        _ = "." + j,
        G = e.fn[M],
        B = { toggle: !0, parent: "" },
        K = { toggle: "boolean", parent: "(string|element)" },
        x = { SHOW: "show" + _, SHOWN: "shown" + _, HIDE: "hide" + _, HIDDEN: "hidden" + _, CLICK_DATA_API: "click" + _ + ".data-api" },
        q = { SHOW: "show", COLLAPSE: "collapse", COLLAPSING: "collapsing", COLLAPSED: "collapsed" },
        Q = { WIDTH: "width", HEIGHT: "height" },
        Y = { ACTIVES: ".show, .collapsing", DATA_TOGGLE: '[data-toggle="collapse"]' },
        X = (function () {
            function t(t, e) {
                (this._isTransitioning = !1),
                    (this._element = t),
                    (this._config = this._getConfig(e)),
                    (this._triggerArray = [].slice.call(document.querySelectorAll('[data-toggle="collapse"][href="#' + t.id + '"],[data-toggle="collapse"][data-target="#' + t.id + '"]')));
                for (var n = [].slice.call(document.querySelectorAll(Y.DATA_TOGGLE)), i = 0, r = n.length; i < r; i++) {
                    var s = n[i],
                        o = h.getSelectorFromElement(s),
                        a = [].slice.call(document.querySelectorAll(o)).filter(function (e) {
                            return e === t;
                        });
                    null !== o && a.length > 0 && ((this._selector = o), this._triggerArray.push(s));
                }
                (this._parent = this._config.parent ? this._getParent() : null), this._config.parent || this._addAriaAndCollapsedClass(this._element, this._triggerArray), this._config.toggle && this.toggle();
            }
            var n = t.prototype;
            return (
                (n.toggle = function t() {
                    e(this._element).hasClass(q.SHOW) ? this.hide() : this.show();
                }),
                (n.show = function n() {
                    var i,
                        r,
                        s = this;
                    if (
                        !(
                            this._isTransitioning ||
                            e(this._element).hasClass(q.SHOW) ||
                            (this._parent &&
                                0 ===
                                    (i = [].slice.call(this._parent.querySelectorAll(Y.ACTIVES)).filter(function (t) {
                                        return "string" == typeof s._config.parent ? t.getAttribute("data-parent") === s._config.parent : t.classList.contains(q.COLLAPSE);
                                    })).length &&
                                (i = null),
                            i && (r = e(i).not(this._selector).data(j)) && r._isTransitioning)
                        )
                    ) {
                        var o = e.Event(x.SHOW);
                        if ((e(this._element).trigger(o), !o.isDefaultPrevented())) {
                            i && (t._jQueryInterface.call(e(i).not(this._selector), "hide"), r || e(i).data(j, null));
                            var a = this._getDimension();
                            e(this._element).removeClass(q.COLLAPSE).addClass(q.COLLAPSING),
                                (this._element.style[a] = 0),
                                this._triggerArray.length && e(this._triggerArray).removeClass(q.COLLAPSED).attr("aria-expanded", !0),
                                this.setTransitioning(!0);
                            var l = function t() {
                                    e(s._element).removeClass(q.COLLAPSING).addClass(q.COLLAPSE).addClass(q.SHOW), (s._element.style[a] = ""), s.setTransitioning(!1), e(s._element).trigger(x.SHOWN);
                                },
                                c = a[0].toUpperCase() + a.slice(1),
                                u = h.getTransitionDurationFromElement(this._element);
                            e(this._element).one(h.TRANSITION_END, l).emulateTransitionEnd(u), (this._element.style[a] = this._element["scroll" + c] + "px");
                        }
                    }
                }),
                (n.hide = function t() {
                    var n = this;
                    if (!this._isTransitioning && e(this._element).hasClass(q.SHOW)) {
                        var i = e.Event(x.HIDE);
                        if ((e(this._element).trigger(i), !i.isDefaultPrevented())) {
                            var r = this._getDimension();
                            (this._element.style[r] = this._element.getBoundingClientRect()[r] + "px"), h.reflow(this._element), e(this._element).addClass(q.COLLAPSING).removeClass(q.COLLAPSE).removeClass(q.SHOW);
                            var s = this._triggerArray.length;
                            if (s > 0)
                                for (var o = 0; o < s; o++) {
                                    var a = this._triggerArray[o],
                                        l = h.getSelectorFromElement(a);
                                    null !== l && (e([].slice.call(document.querySelectorAll(l))).hasClass(q.SHOW) || e(a).addClass(q.COLLAPSED).attr("aria-expanded", !1));
                                }
                            this.setTransitioning(!0);
                            var c = function t() {
                                n.setTransitioning(!1), e(n._element).removeClass(q.COLLAPSING).addClass(q.COLLAPSE).trigger(x.HIDDEN);
                            };
                            this._element.style[r] = "";
                            var u = h.getTransitionDurationFromElement(this._element);
                            e(this._element).one(h.TRANSITION_END, c).emulateTransitionEnd(u);
                        }
                    }
                }),
                (n.setTransitioning = function t(e) {
                    this._isTransitioning = e;
                }),
                (n.dispose = function t() {
                    e.removeData(this._element, j), (this._config = null), (this._parent = null), (this._element = null), (this._triggerArray = null), (this._isTransitioning = null);
                }),
                (n._getConfig = function t(e) {
                    return ((e = a({}, B, {}, e)).toggle = Boolean(e.toggle)), h.typeCheckConfig(M, e, K), e;
                }),
                (n._getDimension = function t() {
                    return e(this._element).hasClass(Q.WIDTH) ? Q.WIDTH : Q.HEIGHT;
                }),
                (n._getParent = function n() {
                    var i,
                        r = this;
                    h.isElement(this._config.parent) ? ((i = this._config.parent), void 0 !== this._config.parent.jquery && (i = this._config.parent[0])) : (i = document.querySelector(this._config.parent));
                    var s = '[data-toggle="collapse"][data-parent="' + this._config.parent + '"]';
                    return (
                        e([].slice.call(i.querySelectorAll(s))).each(function (e, n) {
                            r._addAriaAndCollapsedClass(t._getTargetFromElement(n), [n]);
                        }),
                        i
                    );
                }),
                (n._addAriaAndCollapsedClass = function t(n, i) {
                    var r = e(n).hasClass(q.SHOW);
                    i.length && e(i).toggleClass(q.COLLAPSED, !r).attr("aria-expanded", r);
                }),
                (t._getTargetFromElement = function t(e) {
                    var n = h.getSelectorFromElement(e);
                    return n ? document.querySelector(n) : null;
                }),
                (t._jQueryInterface = function n(i) {
                    return this.each(function () {
                        var n = e(this),
                            r = n.data(j),
                            s = a({}, B, {}, n.data(), {}, "object" == typeof i && i ? i : {});
                        if ((!r && s.toggle && /show|hide/.test(i) && (s.toggle = !1), r || ((r = new t(this, s)), n.data(j, r)), "string" == typeof i)) {
                            if (void 0 === r[i]) throw TypeError('No method named "' + i + '"');
                            r[i]();
                        }
                    });
                }),
                r(t, null, [
                    {
                        key: "VERSION",
                        get: function t() {
                            return "4.4.1";
                        },
                    },
                    {
                        key: "Default",
                        get: function t() {
                            return B;
                        },
                    },
                ]),
                t
            );
        })();
    e(document).on(x.CLICK_DATA_API, Y.DATA_TOGGLE, function (t) {
        "A" === t.currentTarget.tagName && t.preventDefault();
        var n = e(this),
            i = h.getSelectorFromElement(this);
        e([].slice.call(document.querySelectorAll(i))).each(function () {
            var t = e(this),
                i = t.data(j) ? "toggle" : n.data();
            X._jQueryInterface.call(t, i);
        });
    }),
        (e.fn[M] = X._jQueryInterface),
        (e.fn[M].Constructor = X),
        (e.fn[M].noConflict = function () {
            return (e.fn[M] = G), X._jQueryInterface;
        });
    var z = "dropdown",
        J = "bs.dropdown",
        Z = "." + J,
        tt = ".data-api",
        te = e.fn[z],
        tn = RegExp("38|40|27"),
        ti = { HIDE: "hide" + Z, HIDDEN: "hidden" + Z, SHOW: "show" + Z, SHOWN: "shown" + Z, CLICK: "click" + Z, CLICK_DATA_API: "click" + Z + tt, KEYDOWN_DATA_API: "keydown" + Z + tt, KEYUP_DATA_API: "keyup" + Z + tt },
        tr = { DISABLED: "disabled", SHOW: "show", DROPUP: "dropup", DROPRIGHT: "dropright", DROPLEFT: "dropleft", MENURIGHT: "dropdown-menu-right", MENULEFT: "dropdown-menu-left", POSITION_STATIC: "position-static" },
        ts = { DATA_TOGGLE: '[data-toggle="dropdown"]', FORM_CHILD: ".dropdown form", MENU: ".dropdown-menu", NAVBAR_NAV: ".navbar-nav", VISIBLE_ITEMS: ".dropdown-menu .dropdown-item:not(.disabled):not(:disabled)" },
        to = { TOP: "top-start", TOPEND: "top-end", BOTTOM: "bottom-start", BOTTOMEND: "bottom-end", RIGHT: "right-start", RIGHTEND: "right-end", LEFT: "left-start", LEFTEND: "left-end" },
        ta = { offset: 0, flip: !0, boundary: "scrollParent", reference: "toggle", display: "dynamic", popperConfig: null },
        tl = { offset: "(number|string|function)", flip: "boolean", boundary: "(string|element)", reference: "(string|element)", display: "string", popperConfig: "(null|object)" },
        tc = (function () {
            function t(t, e) {
                (this._element = t), (this._popper = null), (this._config = this._getConfig(e)), (this._menu = this._getMenuElement()), (this._inNavbar = this._detectNavbar()), this._addEventListeners();
            }
            var i = t.prototype;
            return (
                (i.toggle = function n() {
                    if (!(this._element.disabled || e(this._element).hasClass(tr.DISABLED))) {
                        var i = e(this._menu).hasClass(tr.SHOW);
                        t._clearMenus(), !i && this.show(!0);
                    }
                }),
                (i.show = function i(r) {
                    if ((void 0 === r && (r = !1), !(this._element.disabled || e(this._element).hasClass(tr.DISABLED) || e(this._menu).hasClass(tr.SHOW)))) {
                        var s = { relatedTarget: this._element },
                            o = e.Event(ti.SHOW, s),
                            a = t._getParentFromElement(this._element);
                        if ((e(a).trigger(o), !o.isDefaultPrevented())) {
                            if (!this._inNavbar && r) {
                                if (void 0 === n) throw TypeError("Bootstrap's dropdowns require Popper.js (https://popper.js.org/)");
                                var l = this._element;
                                "parent" === this._config.reference ? (l = a) : h.isElement(this._config.reference) && ((l = this._config.reference), void 0 !== this._config.reference.jquery && (l = this._config.reference[0])),
                                    "scrollParent" !== this._config.boundary && e(a).addClass(tr.POSITION_STATIC),
                                    (this._popper = new n(l, this._menu, this._getPopperConfig()));
                            }
                            "ontouchstart" in document.documentElement && 0 === e(a).closest(ts.NAVBAR_NAV).length && e(document.body).children().on("mouseover", null, e.noop),
                                this._element.focus(),
                                this._element.setAttribute("aria-expanded", !0),
                                e(this._menu).toggleClass(tr.SHOW),
                                e(a).toggleClass(tr.SHOW).trigger(e.Event(ti.SHOWN, s));
                        }
                    }
                }),
                (i.hide = function n() {
                    if (!(this._element.disabled || e(this._element).hasClass(tr.DISABLED)) && e(this._menu).hasClass(tr.SHOW)) {
                        var i = { relatedTarget: this._element },
                            r = e.Event(ti.HIDE, i),
                            s = t._getParentFromElement(this._element);
                        e(s).trigger(r), !r.isDefaultPrevented() && (this._popper && this._popper.destroy(), e(this._menu).toggleClass(tr.SHOW), e(s).toggleClass(tr.SHOW).trigger(e.Event(ti.HIDDEN, i)));
                    }
                }),
                (i.dispose = function t() {
                    e.removeData(this._element, J), e(this._element).off(Z), (this._element = null), (this._menu = null), null !== this._popper && (this._popper.destroy(), (this._popper = null));
                }),
                (i.update = function t() {
                    (this._inNavbar = this._detectNavbar()), null !== this._popper && this._popper.scheduleUpdate();
                }),
                (i._addEventListeners = function t() {
                    var n = this;
                    e(this._element).on(ti.CLICK, function (t) {
                        t.preventDefault(), t.stopPropagation(), n.toggle();
                    });
                }),
                (i._getConfig = function t(n) {
                    return (n = a({}, this.constructor.Default, {}, e(this._element).data(), {}, n)), h.typeCheckConfig(z, n, this.constructor.DefaultType), n;
                }),
                (i._getMenuElement = function e() {
                    if (!this._menu) {
                        var n = t._getParentFromElement(this._element);
                        n && (this._menu = n.querySelector(ts.MENU));
                    }
                    return this._menu;
                }),
                (i._getPlacement = function t() {
                    var n = e(this._element.parentNode),
                        i = to.BOTTOM;
                    return (
                        n.hasClass(tr.DROPUP)
                            ? ((i = to.TOP), e(this._menu).hasClass(tr.MENURIGHT) && (i = to.TOPEND))
                            : n.hasClass(tr.DROPRIGHT)
                            ? (i = to.RIGHT)
                            : n.hasClass(tr.DROPLEFT)
                            ? (i = to.LEFT)
                            : e(this._menu).hasClass(tr.MENURIGHT) && (i = to.BOTTOMEND),
                        i
                    );
                }),
                (i._detectNavbar = function t() {
                    return e(this._element).closest(".navbar").length > 0;
                }),
                (i._getOffset = function t() {
                    var e = this,
                        n = {};
                    return (
                        "function" == typeof this._config.offset
                            ? (n.fn = function (t) {
                                  return (t.offsets = a({}, t.offsets, {}, e._config.offset(t.offsets, e._element) || {})), t;
                              })
                            : (n.offset = this._config.offset),
                        n
                    );
                }),
                (i._getPopperConfig = function t() {
                    var e = { placement: this._getPlacement(), modifiers: { offset: this._getOffset(), flip: { enabled: this._config.flip }, preventOverflow: { boundariesElement: this._config.boundary } } };
                    return "static" === this._config.display && (e.modifiers.applyStyle = { enabled: !1 }), a({}, e, {}, this._config.popperConfig);
                }),
                (t._jQueryInterface = function n(i) {
                    return this.each(function () {
                        var n = e(this).data(J);
                        if ((n || ((n = new t(this, "object" == typeof i ? i : null)), e(this).data(J, n)), "string" == typeof i)) {
                            if (void 0 === n[i]) throw TypeError('No method named "' + i + '"');
                            n[i]();
                        }
                    });
                }),
                (t._clearMenus = function n(i) {
                    if (!i || (3 !== i.which && ("keyup" !== i.type || 9 === i.which)))
                        for (var r = [].slice.call(document.querySelectorAll(ts.DATA_TOGGLE)), s = 0, o = r.length; s < o; s++) {
                            var a = t._getParentFromElement(r[s]),
                                l = e(r[s]).data(J),
                                c = { relatedTarget: r[s] };
                            if ((i && "click" === i.type && (c.clickEvent = i), l)) {
                                var h = l._menu;
                                if (!(!e(a).hasClass(tr.SHOW) || (i && (("click" === i.type && /input|textarea/i.test(i.target.tagName)) || ("keyup" === i.type && 9 === i.which)) && e.contains(a, i.target)))) {
                                    var u = e.Event(ti.HIDE, c);
                                    e(a).trigger(u),
                                        !u.isDefaultPrevented() &&
                                            ("ontouchstart" in document.documentElement && e(document.body).children().off("mouseover", null, e.noop),
                                            r[s].setAttribute("aria-expanded", "false"),
                                            l._popper && l._popper.destroy(),
                                            e(h).removeClass(tr.SHOW),
                                            e(a).removeClass(tr.SHOW).trigger(e.Event(ti.HIDDEN, c)));
                                }
                            }
                        }
                }),
                (t._getParentFromElement = function t(e) {
                    var n,
                        i = h.getSelectorFromElement(e);
                    return i && (n = document.querySelector(i)), n || e.parentNode;
                }),
                (t._dataApiKeydownHandler = function n(i) {
                    if (
                        !(
                            (/input|textarea/i.test(i.target.tagName) ? 32 === i.which || (27 !== i.which && ((40 !== i.which && 38 !== i.which) || e(i.target).closest(ts.MENU).length)) : !tn.test(i.which)) ||
                            (i.preventDefault(), i.stopPropagation(), this.disabled || e(this).hasClass(tr.DISABLED))
                        )
                    ) {
                        var r = t._getParentFromElement(this),
                            s = e(r).hasClass(tr.SHOW);
                        if (s || 27 !== i.which) {
                            if (!s || (s && (27 === i.which || 32 === i.which))) {
                                27 === i.which && e(r.querySelector(ts.DATA_TOGGLE)).trigger("focus"), e(this).trigger("click");
                                return;
                            }
                            var o = [].slice.call(r.querySelectorAll(ts.VISIBLE_ITEMS)).filter(function (t) {
                                return e(t).is(":visible");
                            });
                            if (0 !== o.length) {
                                var a = o.indexOf(i.target);
                                38 === i.which && a > 0 && a--, 40 === i.which && a < o.length - 1 && a++, a < 0 && (a = 0), o[a].focus();
                            }
                        }
                    }
                }),
                r(t, null, [
                    {
                        key: "VERSION",
                        get: function t() {
                            return "4.4.1";
                        },
                    },
                    {
                        key: "Default",
                        get: function t() {
                            return ta;
                        },
                    },
                    {
                        key: "DefaultType",
                        get: function t() {
                            return tl;
                        },
                    },
                ]),
                t
            );
        })();
    e(document)
        .on(ti.KEYDOWN_DATA_API, ts.DATA_TOGGLE, tc._dataApiKeydownHandler)
        .on(ti.KEYDOWN_DATA_API, ts.MENU, tc._dataApiKeydownHandler)
        .on(ti.CLICK_DATA_API + " " + ti.KEYUP_DATA_API, tc._clearMenus)
        .on(ti.CLICK_DATA_API, ts.DATA_TOGGLE, function (t) {
            t.preventDefault(), t.stopPropagation(), tc._jQueryInterface.call(e(this), "toggle");
        })
        .on(ti.CLICK_DATA_API, ts.FORM_CHILD, function (t) {
            t.stopPropagation();
        }),
        (e.fn[z] = tc._jQueryInterface),
        (e.fn[z].Constructor = tc),
        (e.fn[z].noConflict = function () {
            return (e.fn[z] = te), tc._jQueryInterface;
        });
    var th = "modal",
        tu = "bs.modal",
        tf = "." + tu,
        t8 = e.fn[th],
        td = { backdrop: !0, keyboard: !0, focus: !0, show: !0 },
        tg = { backdrop: "(boolean|string)", keyboard: "boolean", focus: "boolean", show: "boolean" },
        tm = {
            HIDE: "hide" + tf,
            HIDE_PREVENTED: "hidePrevented" + tf,
            HIDDEN: "hidden" + tf,
            SHOW: "show" + tf,
            SHOWN: "shown" + tf,
            FOCUSIN: "focusin" + tf,
            RESIZE: "resize" + tf,
            CLICK_DISMISS: "click.dismiss" + tf,
            KEYDOWN_DISMISS: "keydown.dismiss" + tf,
            MOUSEUP_DISMISS: "mouseup.dismiss" + tf,
            MOUSEDOWN_DISMISS: "mousedown.dismiss" + tf,
            CLICK_DATA_API: "click" + tf + ".data-api",
        },
        tp = { SCROLLABLE: "modal-dialog-scrollable", SCROLLBAR_MEASURER: "modal-scrollbar-measure", BACKDROP: "modal-backdrop", OPEN: "modal-open", FADE: "fade", SHOW: "show", STATIC: "modal-static" },
        tE = {
            DIALOG: ".modal-dialog",
            MODAL_BODY: ".modal-body",
            DATA_TOGGLE: '[data-toggle="modal"]',
            DATA_DISMISS: '[data-dismiss="modal"]',
            FIXED_CONTENT: ".fixed-top, .fixed-bottom, .is-fixed, .sticky-top",
            STICKY_CONTENT: ".sticky-top",
        },
        tv = (function () {
            function t(t, e) {
                (this._config = this._getConfig(e)),
                    (this._element = t),
                    (this._dialog = t.querySelector(tE.DIALOG)),
                    (this._backdrop = null),
                    (this._isShown = !1),
                    (this._isBodyOverflowing = !1),
                    (this._ignoreBackdropClick = !1),
                    (this._isTransitioning = !1),
                    (this._scrollbarWidth = 0);
            }
            var n = t.prototype;
            return (
                (n.toggle = function t(e) {
                    return this._isShown ? this.hide() : this.show(e);
                }),
                (n.show = function t(n) {
                    var i = this;
                    if (!this._isShown && !this._isTransitioning) {
                        e(this._element).hasClass(tp.FADE) && (this._isTransitioning = !0);
                        var r = e.Event(tm.SHOW, { relatedTarget: n });
                        e(this._element).trigger(r),
                            !(this._isShown || r.isDefaultPrevented()) &&
                                ((this._isShown = !0),
                                this._checkScrollbar(),
                                this._setScrollbar(),
                                this._adjustDialog(),
                                this._setEscapeEvent(),
                                this._setResizeEvent(),
                                e(this._element).on(tm.CLICK_DISMISS, tE.DATA_DISMISS, function (t) {
                                    return i.hide(t);
                                }),
                                e(this._dialog).on(tm.MOUSEDOWN_DISMISS, function () {
                                    e(i._element).one(tm.MOUSEUP_DISMISS, function (t) {
                                        e(t.target).is(i._element) && (i._ignoreBackdropClick = !0);
                                    });
                                }),
                                this._showBackdrop(function () {
                                    return i._showElement(n);
                                }));
                    }
                }),
                (n.hide = function t(n) {
                    var i = this;
                    if ((n && n.preventDefault(), this._isShown && !this._isTransitioning)) {
                        var r = e.Event(tm.HIDE);
                        if ((e(this._element).trigger(r), !(!this._isShown || r.isDefaultPrevented()))) {
                            this._isShown = !1;
                            var s = e(this._element).hasClass(tp.FADE);
                            if (
                                (s && (this._isTransitioning = !0),
                                this._setEscapeEvent(),
                                this._setResizeEvent(),
                                e(document).off(tm.FOCUSIN),
                                e(this._element).removeClass(tp.SHOW),
                                e(this._element).off(tm.CLICK_DISMISS),
                                e(this._dialog).off(tm.MOUSEDOWN_DISMISS),
                                s)
                            ) {
                                var o = h.getTransitionDurationFromElement(this._element);
                                e(this._element)
                                    .one(h.TRANSITION_END, function (t) {
                                        return i._hideModal(t);
                                    })
                                    .emulateTransitionEnd(o);
                            } else this._hideModal();
                        }
                    }
                }),
                (n.dispose = function t() {
                    [window, this._element, this._dialog].forEach(function (t) {
                        return e(t).off(tf);
                    }),
                        e(document).off(tm.FOCUSIN),
                        e.removeData(this._element, tu),
                        (this._config = null),
                        (this._element = null),
                        (this._dialog = null),
                        (this._backdrop = null),
                        (this._isShown = null),
                        (this._isBodyOverflowing = null),
                        (this._ignoreBackdropClick = null),
                        (this._isTransitioning = null),
                        (this._scrollbarWidth = null);
                }),
                (n.handleUpdate = function t() {
                    this._adjustDialog();
                }),
                (n._getConfig = function t(e) {
                    return (e = a({}, td, {}, e)), h.typeCheckConfig(th, e, tg), e;
                }),
                (n._triggerBackdropTransition = function t() {
                    var n = this;
                    if ("static" === this._config.backdrop) {
                        var i = e.Event(tm.HIDE_PREVENTED);
                        if ((e(this._element).trigger(i), i.defaultPrevented)) return;
                        this._element.classList.add(tp.STATIC);
                        var r = h.getTransitionDurationFromElement(this._element);
                        e(this._element)
                            .one(h.TRANSITION_END, function () {
                                n._element.classList.remove(tp.STATIC);
                            })
                            .emulateTransitionEnd(r),
                            this._element.focus();
                    } else this.hide();
                }),
                (n._showElement = function t(n) {
                    var i = this,
                        r = e(this._element).hasClass(tp.FADE),
                        s = this._dialog ? this._dialog.querySelector(tE.MODAL_BODY) : null;
                    (this._element.parentNode && this._element.parentNode.nodeType === Node.ELEMENT_NODE) || document.body.appendChild(this._element),
                        (this._element.style.display = "block"),
                        this._element.removeAttribute("aria-hidden"),
                        this._element.setAttribute("aria-modal", !0),
                        e(this._dialog).hasClass(tp.SCROLLABLE) && s ? (s.scrollTop = 0) : (this._element.scrollTop = 0),
                        r && h.reflow(this._element),
                        e(this._element).addClass(tp.SHOW),
                        this._config.focus && this._enforceFocus();
                    var o = e.Event(tm.SHOWN, { relatedTarget: n }),
                        a = function t() {
                            i._config.focus && i._element.focus(), (i._isTransitioning = !1), e(i._element).trigger(o);
                        };
                    if (r) {
                        var l = h.getTransitionDurationFromElement(this._dialog);
                        e(this._dialog).one(h.TRANSITION_END, a).emulateTransitionEnd(l);
                    } else a();
                }),
                (n._enforceFocus = function t() {
                    var n = this;
                    e(document)
                        .off(tm.FOCUSIN)
                        .on(tm.FOCUSIN, function (t) {
                            document !== t.target && n._element !== t.target && 0 === e(n._element).has(t.target).length && n._element.focus();
                        });
                }),
                (n._setEscapeEvent = function t() {
                    var n = this;
                    this._isShown && this._config.keyboard
                        ? e(this._element).on(tm.KEYDOWN_DISMISS, function (t) {
                              27 === t.which && n._triggerBackdropTransition();
                          })
                        : this._isShown || e(this._element).off(tm.KEYDOWN_DISMISS);
                }),
                (n._setResizeEvent = function t() {
                    var n = this;
                    this._isShown
                        ? e(window).on(tm.RESIZE, function (t) {
                              return n.handleUpdate(t);
                          })
                        : e(window).off(tm.RESIZE);
                }),
                (n._hideModal = function t() {
                    var n = this;
                    (this._element.style.display = "none"),
                        this._element.setAttribute("aria-hidden", !0),
                        this._element.removeAttribute("aria-modal"),
                        (this._isTransitioning = !1),
                        this._showBackdrop(function () {
                            e(document.body).removeClass(tp.OPEN), n._resetAdjustments(), n._resetScrollbar(), e(n._element).trigger(tm.HIDDEN);
                        });
                }),
                (n._removeBackdrop = function t() {
                    this._backdrop && (e(this._backdrop).remove(), (this._backdrop = null));
                }),
                (n._showBackdrop = function t(n) {
                    var i = this,
                        r = e(this._element).hasClass(tp.FADE) ? tp.FADE : "";
                    if (this._isShown && this._config.backdrop) {
                        if (
                            ((this._backdrop = document.createElement("div")),
                            (this._backdrop.className = tp.BACKDROP),
                            r && this._backdrop.classList.add(r),
                            e(this._backdrop).appendTo(document.body),
                            e(this._element).on(tm.CLICK_DISMISS, function (t) {
                                if (i._ignoreBackdropClick) {
                                    i._ignoreBackdropClick = !1;
                                    return;
                                }
                                t.target === t.currentTarget && i._triggerBackdropTransition();
                            }),
                            r && h.reflow(this._backdrop),
                            e(this._backdrop).addClass(tp.SHOW),
                            !n)
                        )
                            return;
                        if (!r) {
                            n();
                            return;
                        }
                        var s = h.getTransitionDurationFromElement(this._backdrop);
                        e(this._backdrop).one(h.TRANSITION_END, n).emulateTransitionEnd(s);
                    } else if (!this._isShown && this._backdrop) {
                        e(this._backdrop).removeClass(tp.SHOW);
                        var o = function t() {
                            i._removeBackdrop(), n && n();
                        };
                        if (e(this._element).hasClass(tp.FADE)) {
                            var a = h.getTransitionDurationFromElement(this._backdrop);
                            e(this._backdrop).one(h.TRANSITION_END, o).emulateTransitionEnd(a);
                        } else o();
                    } else n && n();
                }),
                (n._adjustDialog = function t() {
                    var e = this._element.scrollHeight > document.documentElement.clientHeight;
                    !this._isBodyOverflowing && e && (this._element.style.paddingLeft = this._scrollbarWidth + "px"), this._isBodyOverflowing && !e && (this._element.style.paddingRight = this._scrollbarWidth + "px");
                }),
                (n._resetAdjustments = function t() {
                    (this._element.style.paddingLeft = ""), (this._element.style.paddingRight = "");
                }),
                (n._checkScrollbar = function t() {
                    var e = document.body.getBoundingClientRect();
                    (this._isBodyOverflowing = e.left + e.right < window.innerWidth), (this._scrollbarWidth = this._getScrollbarWidth());
                }),
                (n._setScrollbar = function t() {
                    var n = this;
                    if (this._isBodyOverflowing) {
                        var i = [].slice.call(document.querySelectorAll(tE.FIXED_CONTENT)),
                            r = [].slice.call(document.querySelectorAll(tE.STICKY_CONTENT));
                        e(i).each(function (t, i) {
                            var r = i.style.paddingRight,
                                s = e(i).css("padding-right");
                            e(i)
                                .data("padding-right", r)
                                .css("padding-right", parseFloat(s) + n._scrollbarWidth + "px");
                        }),
                            e(r).each(function (t, i) {
                                var r = i.style.marginRight,
                                    s = e(i).css("margin-right");
                                e(i)
                                    .data("margin-right", r)
                                    .css("margin-right", parseFloat(s) - n._scrollbarWidth + "px");
                            });
                        var s = document.body.style.paddingRight,
                            o = e(document.body).css("padding-right");
                        e(document.body)
                            .data("padding-right", s)
                            .css("padding-right", parseFloat(o) + this._scrollbarWidth + "px");
                    }
                    e(document.body).addClass(tp.OPEN);
                }),
                (n._resetScrollbar = function t() {
                    e([].slice.call(document.querySelectorAll(tE.FIXED_CONTENT))).each(function (t, n) {
                        var i = e(n).data("padding-right");
                        e(n).removeData("padding-right"), (n.style.paddingRight = i || "");
                    }),
                        e([].slice.call(document.querySelectorAll("" + tE.STICKY_CONTENT))).each(function (t, n) {
                            var i = e(n).data("margin-right");
                            void 0 !== i && e(n).css("margin-right", i).removeData("margin-right");
                        });
                    var n = e(document.body).data("padding-right");
                    e(document.body).removeData("padding-right"), (document.body.style.paddingRight = n || "");
                }),
                (n._getScrollbarWidth = function t() {
                    var e = document.createElement("div");
                    (e.className = tp.SCROLLBAR_MEASURER), document.body.appendChild(e);
                    var n = e.getBoundingClientRect().width - e.clientWidth;
                    return document.body.removeChild(e), n;
                }),
                (t._jQueryInterface = function n(i, r) {
                    return this.each(function () {
                        var n = e(this).data(tu),
                            s = a({}, td, {}, e(this).data(), {}, "object" == typeof i && i ? i : {});
                        if ((n || ((n = new t(this, s)), e(this).data(tu, n)), "string" == typeof i)) {
                            if (void 0 === n[i]) throw TypeError('No method named "' + i + '"');
                            n[i](r);
                        } else s.show && n.show(r);
                    });
                }),
                r(t, null, [
                    {
                        key: "VERSION",
                        get: function t() {
                            return "4.4.1";
                        },
                    },
                    {
                        key: "Default",
                        get: function t() {
                            return td;
                        },
                    },
                ]),
                t
            );
        })();
    e(document).on(tm.CLICK_DATA_API, tE.DATA_TOGGLE, function (t) {
        var n,
            i = this,
            r = h.getSelectorFromElement(this);
        r && (n = document.querySelector(r));
        var s = e(n).data(tu) ? "toggle" : a({}, e(n).data(), {}, e(this).data());
        ("A" === this.tagName || "AREA" === this.tagName) && t.preventDefault();
        var o = e(n).one(tm.SHOW, function (t) {
            !t.isDefaultPrevented() &&
                o.one(tm.HIDDEN, function () {
                    e(i).is(":visible") && i.focus();
                });
        });
        tv._jQueryInterface.call(e(n), s, this);
    }),
        (e.fn[th] = tv._jQueryInterface),
        (e.fn[th].Constructor = tv),
        (e.fn[th].noConflict = function () {
            return (e.fn[th] = t8), tv._jQueryInterface;
        });
    var tT = ["background", "cite", "href", "itemtype", "longdesc", "poster", "src", "xlink:href"],
        tA = /^(?:(?:https?|mailto|ftp|tel|file):|[^&:/?#]*(?:[/?#]|$))/gi,
        tI = /^data:(?:image\/(?:bmp|gif|jpeg|jpg|png|tiff|webp)|video\/(?:mpeg|mp4|ogg|webm)|audio\/(?:mp3|oga|ogg|opus));base64,[a-z0-9+/]+=*$/i;
    function tS(t, e, n) {
        if (0 === t.length) return t;
        if (n && "function" == typeof n) return n(t);
        for (var i = new window.DOMParser().parseFromString(t, "text/html"), r = Object.keys(e), s = [].slice.call(i.body.querySelectorAll("*")), o = 0, a = s.length; o < a; o++)
            if (
                "continue" ===
                (function t(n, i) {
                    var o = s[n],
                        a = o.nodeName.toLowerCase();
                    if (-1 === r.indexOf(o.nodeName.toLowerCase())) return o.parentNode.removeChild(o), "continue";
                    var l = [].slice.call(o.attributes),
                        c = [].concat(e["*"] || [], e[a] || []);
                    l.forEach(function (t) {
                        !(function t(e, n) {
                            var i = e.nodeName.toLowerCase();
                            if (-1 !== n.indexOf(i)) return -1 === tT.indexOf(i) || Boolean(e.nodeValue.match(tA) || e.nodeValue.match(tI));
                            for (
                                var r = n.filter(function (t) {
                                        return t instanceof RegExp;
                                    }),
                                    s = 0,
                                    o = r.length;
                                s < o;
                                s++
                            )
                                if (i.match(r[s])) return !0;
                            return !1;
                        })(t, c) && o.removeAttribute(t.nodeName);
                    });
                })(o)
            )
                continue;
        return i.body.innerHTML;
    }
    var tC = "tooltip",
        tD = "bs.tooltip",
        tO = "." + tD,
        ty = e.fn[tC],
        tN = "bs-tooltip",
        tb = RegExp("(^|\\s)" + tN + "\\S+", "g"),
        tL = ["sanitize", "whiteList", "sanitizeFn"],
        tw = {
            animation: "boolean",
            template: "string",
            title: "(string|element|function)",
            trigger: "string",
            delay: "(number|object)",
            html: "boolean",
            selector: "(string|boolean)",
            placement: "(string|function)",
            offset: "(number|string|function)",
            container: "(string|element|boolean)",
            fallbackPlacement: "(string|array)",
            boundary: "(string|element)",
            sanitize: "boolean",
            sanitizeFn: "(null|function)",
            whiteList: "object",
            popperConfig: "(null|object)",
        },
        tP = { AUTO: "auto", TOP: "top", RIGHT: "right", BOTTOM: "bottom", LEFT: "left" },
        tH = {
            animation: !0,
            template: '<div class="tooltip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
            trigger: "hover focus",
            title: "",
            delay: 0,
            html: !1,
            selector: !1,
            placement: "top",
            offset: 0,
            container: !1,
            fallbackPlacement: "flip",
            boundary: "scrollParent",
            sanitize: !0,
            sanitizeFn: null,
            whiteList: {
                "*": ["class", "dir", "id", "lang", "role", /^aria-[\w-]*$/i],
                a: ["target", "href", "title", "rel"],
                area: [],
                b: [],
                br: [],
                col: [],
                code: [],
                div: [],
                em: [],
                hr: [],
                h1: [],
                h2: [],
                h3: [],
                h4: [],
                h5: [],
                h6: [],
                i: [],
                img: ["src", "alt", "title", "width", "height"],
                li: [],
                ol: [],
                p: [],
                pre: [],
                s: [],
                small: [],
                span: [],
                sub: [],
                sup: [],
                strong: [],
                u: [],
                ul: [],
            },
            popperConfig: null,
        },
        tR = { SHOW: "show", OUT: "out" },
        t$ = {
            HIDE: "hide" + tO,
            HIDDEN: "hidden" + tO,
            SHOW: "show" + tO,
            SHOWN: "shown" + tO,
            INSERTED: "inserted" + tO,
            CLICK: "click" + tO,
            FOCUSIN: "focusin" + tO,
            FOCUSOUT: "focusout" + tO,
            MOUSEENTER: "mouseenter" + tO,
            MOUSELEAVE: "mouseleave" + tO,
        },
        tk = { FADE: "fade", SHOW: "show" },
        tW = { TOOLTIP: ".tooltip", TOOLTIP_INNER: ".tooltip-inner", ARROW: ".arrow" },
        tV = { HOVER: "hover", FOCUS: "focus", CLICK: "click", MANUAL: "manual" },
        tU = (function () {
            function t(t, e) {
                if (void 0 === n) throw TypeError("Bootstrap's tooltips require Popper.js (https://popper.js.org/)");
                (this._isEnabled = !0), (this._timeout = 0), (this._hoverState = ""), (this._activeTrigger = {}), (this._popper = null), (this.element = t), (this.config = this._getConfig(e)), (this.tip = null), this._setListeners();
            }
            var i = t.prototype;
            return (
                (i.enable = function t() {
                    this._isEnabled = !0;
                }),
                (i.disable = function t() {
                    this._isEnabled = !1;
                }),
                (i.toggleEnabled = function t() {
                    this._isEnabled = !this._isEnabled;
                }),
                (i.toggle = function t(n) {
                    if (this._isEnabled) {
                        if (n) {
                            var i = this.constructor.DATA_KEY,
                                r = e(n.currentTarget).data(i);
                            r || ((r = new this.constructor(n.currentTarget, this._getDelegateConfig())), e(n.currentTarget).data(i, r)),
                                (r._activeTrigger.click = !r._activeTrigger.click),
                                r._isWithActiveTrigger() ? r._enter(null, r) : r._leave(null, r);
                        } else {
                            if (e(this.getTipElement()).hasClass(tk.SHOW)) {
                                this._leave(null, this);
                                return;
                            }
                            this._enter(null, this);
                        }
                    }
                }),
                (i.dispose = function t() {
                    clearTimeout(this._timeout),
                        e.removeData(this.element, this.constructor.DATA_KEY),
                        e(this.element).off(this.constructor.EVENT_KEY),
                        e(this.element).closest(".modal").off("hide.bs.modal", this._hideModalHandler),
                        this.tip && e(this.tip).remove(),
                        (this._isEnabled = null),
                        (this._timeout = null),
                        (this._hoverState = null),
                        (this._activeTrigger = null),
                        this._popper && this._popper.destroy(),
                        (this._popper = null),
                        (this.element = null),
                        (this.config = null),
                        (this.tip = null);
                }),
                (i.show = function t() {
                    var i = this;
                    if ("none" === e(this.element).css("display")) throw Error("Please use show on visible elements");
                    var r = e.Event(this.constructor.Event.SHOW);
                    if (this.isWithContent() && this._isEnabled) {
                        e(this.element).trigger(r);
                        var s = h.findShadowRoot(this.element),
                            o = e.contains(null !== s ? s : this.element.ownerDocument.documentElement, this.element);
                        if (r.isDefaultPrevented() || !o) return;
                        var a = this.getTipElement(),
                            l = h.getUID(this.constructor.NAME);
                        a.setAttribute("id", l), this.element.setAttribute("aria-describedby", l), this.setContent(), this.config.animation && e(a).addClass(tk.FADE);
                        var c = "function" == typeof this.config.placement ? this.config.placement.call(this, a, this.element) : this.config.placement,
                            u = this._getAttachment(c);
                        this.addAttachmentClass(u);
                        var f = this._getContainer();
                        e(a).data(this.constructor.DATA_KEY, this),
                            e.contains(this.element.ownerDocument.documentElement, this.tip) || e(a).appendTo(f),
                            e(this.element).trigger(this.constructor.Event.INSERTED),
                            (this._popper = new n(this.element, a, this._getPopperConfig(u))),
                            e(a).addClass(tk.SHOW),
                            "ontouchstart" in document.documentElement && e(document.body).children().on("mouseover", null, e.noop);
                        var d = function t() {
                            i.config.animation && i._fixTransition();
                            var n = i._hoverState;
                            (i._hoverState = null), e(i.element).trigger(i.constructor.Event.SHOWN), n === tR.OUT && i._leave(null, i);
                        };
                        if (e(this.tip).hasClass(tk.FADE)) {
                            var g = h.getTransitionDurationFromElement(this.tip);
                            e(this.tip).one(h.TRANSITION_END, d).emulateTransitionEnd(g);
                        } else d();
                    }
                }),
                (i.hide = function t(n) {
                    var i = this,
                        r = this.getTipElement(),
                        s = e.Event(this.constructor.Event.HIDE),
                        o = function t() {
                            i._hoverState !== tR.SHOW && r.parentNode && r.parentNode.removeChild(r),
                                i._cleanTipClass(),
                                i.element.removeAttribute("aria-describedby"),
                                e(i.element).trigger(i.constructor.Event.HIDDEN),
                                null !== i._popper && i._popper.destroy(),
                                n && n();
                        };
                    if ((e(this.element).trigger(s), !s.isDefaultPrevented())) {
                        if (
                            (e(r).removeClass(tk.SHOW),
                            "ontouchstart" in document.documentElement && e(document.body).children().off("mouseover", null, e.noop),
                            (this._activeTrigger[tV.CLICK] = !1),
                            (this._activeTrigger[tV.FOCUS] = !1),
                            (this._activeTrigger[tV.HOVER] = !1),
                            e(this.tip).hasClass(tk.FADE))
                        ) {
                            var a = h.getTransitionDurationFromElement(r);
                            e(r).one(h.TRANSITION_END, o).emulateTransitionEnd(a);
                        } else o();
                        this._hoverState = "";
                    }
                }),
                (i.update = function t() {
                    null !== this._popper && this._popper.scheduleUpdate();
                }),
                (i.isWithContent = function t() {
                    return Boolean(this.getTitle());
                }),
                (i.addAttachmentClass = function t(n) {
                    e(this.getTipElement()).addClass(tN + "-" + n);
                }),
                (i.getTipElement = function t() {
                    return (this.tip = this.tip || e(this.config.template)[0]), this.tip;
                }),
                (i.setContent = function t() {
                    var n = this.getTipElement();
                    this.setElementContent(e(n.querySelectorAll(tW.TOOLTIP_INNER)), this.getTitle()), e(n).removeClass(tk.FADE + " " + tk.SHOW);
                }),
                (i.setElementContent = function t(n, i) {
                    if ("object" == typeof i && (i.nodeType || i.jquery)) {
                        this.config.html ? e(i).parent().is(n) || n.empty().append(i) : n.text(e(i).text());
                        return;
                    }
                    this.config.html ? (this.config.sanitize && (i = tS(i, this.config.whiteList, this.config.sanitizeFn)), n.html(i)) : n.text(i);
                }),
                (i.getTitle = function t() {
                    var e = this.element.getAttribute("data-original-title");
                    return e || (e = "function" == typeof this.config.title ? this.config.title.call(this.element) : this.config.title), e;
                }),
                (i._getPopperConfig = function t(e) {
                    var n = this,
                        i = {
                            placement: e,
                            modifiers: { offset: this._getOffset(), flip: { behavior: this.config.fallbackPlacement }, arrow: { element: tW.ARROW }, preventOverflow: { boundariesElement: this.config.boundary } },
                            onCreate: function t(e) {
                                e.originalPlacement !== e.placement && n._handlePopperPlacementChange(e);
                            },
                            onUpdate: function t(e) {
                                return n._handlePopperPlacementChange(e);
                            },
                        };
                    return a({}, i, {}, this.config.popperConfig);
                }),
                (i._getOffset = function t() {
                    var e = this,
                        n = {};
                    return (
                        "function" == typeof this.config.offset
                            ? (n.fn = function (t) {
                                  return (t.offsets = a({}, t.offsets, {}, e.config.offset(t.offsets, e.element) || {})), t;
                              })
                            : (n.offset = this.config.offset),
                        n
                    );
                }),
                (i._getContainer = function t() {
                    return !1 === this.config.container ? document.body : h.isElement(this.config.container) ? e(this.config.container) : e(document).find(this.config.container);
                }),
                (i._getAttachment = function t(e) {
                    return tP[e.toUpperCase()];
                }),
                (i._setListeners = function t() {
                    var n = this;
                    this.config.trigger.split(" ").forEach(function (t) {
                        if ("click" === t)
                            e(n.element).on(n.constructor.Event.CLICK, n.config.selector, function (t) {
                                return n.toggle(t);
                            });
                        else if (t !== tV.MANUAL) {
                            var i = t === tV.HOVER ? n.constructor.Event.MOUSEENTER : n.constructor.Event.FOCUSIN,
                                r = t === tV.HOVER ? n.constructor.Event.MOUSELEAVE : n.constructor.Event.FOCUSOUT;
                            e(n.element)
                                .on(i, n.config.selector, function (t) {
                                    return n._enter(t);
                                })
                                .on(r, n.config.selector, function (t) {
                                    return n._leave(t);
                                });
                        }
                    }),
                        (this._hideModalHandler = function () {
                            n.element && n.hide();
                        }),
                        e(this.element).closest(".modal").on("hide.bs.modal", this._hideModalHandler),
                        this.config.selector ? (this.config = a({}, this.config, { trigger: "manual", selector: "" })) : this._fixTitle();
                }),
                (i._fixTitle = function t() {
                    var e = typeof this.element.getAttribute("data-original-title");
                    (this.element.getAttribute("title") || "string" !== e) && (this.element.setAttribute("data-original-title", this.element.getAttribute("title") || ""), this.element.setAttribute("title", ""));
                }),
                (i._enter = function t(n, i) {
                    var r = this.constructor.DATA_KEY;
                    if (
                        ((i = i || e(n.currentTarget).data(r)) || ((i = new this.constructor(n.currentTarget, this._getDelegateConfig())), e(n.currentTarget).data(r, i)),
                        n && (i._activeTrigger["focusin" === n.type ? tV.FOCUS : tV.HOVER] = !0),
                        e(i.getTipElement()).hasClass(tk.SHOW) || i._hoverState === tR.SHOW)
                    ) {
                        i._hoverState = tR.SHOW;
                        return;
                    }
                    if ((clearTimeout(i._timeout), (i._hoverState = tR.SHOW), !i.config.delay || !i.config.delay.show)) {
                        i.show();
                        return;
                    }
                    i._timeout = setTimeout(function () {
                        i._hoverState === tR.SHOW && i.show();
                    }, i.config.delay.show);
                }),
                (i._leave = function t(n, i) {
                    var r = this.constructor.DATA_KEY;
                    if (
                        ((i = i || e(n.currentTarget).data(r)) || ((i = new this.constructor(n.currentTarget, this._getDelegateConfig())), e(n.currentTarget).data(r, i)),
                        n && (i._activeTrigger["focusout" === n.type ? tV.FOCUS : tV.HOVER] = !1),
                        !i._isWithActiveTrigger())
                    ) {
                        if ((clearTimeout(i._timeout), (i._hoverState = tR.OUT), !i.config.delay || !i.config.delay.hide)) {
                            i.hide();
                            return;
                        }
                        i._timeout = setTimeout(function () {
                            i._hoverState === tR.OUT && i.hide();
                        }, i.config.delay.hide);
                    }
                }),
                (i._isWithActiveTrigger = function t() {
                    for (var e in this._activeTrigger) if (this._activeTrigger[e]) return !0;
                    return !1;
                }),
                (i._getConfig = function t(n) {
                    var i = e(this.element).data();
                    return (
                        Object.keys(i).forEach(function (t) {
                            -1 !== tL.indexOf(t) && delete i[t];
                        }),
                        "number" == typeof (n = a({}, this.constructor.Default, {}, i, {}, "object" == typeof n && n ? n : {})).delay && (n.delay = { show: n.delay, hide: n.delay }),
                        "number" == typeof n.title && (n.title = n.title.toString()),
                        "number" == typeof n.content && (n.content = n.content.toString()),
                        h.typeCheckConfig(tC, n, this.constructor.DefaultType),
                        n.sanitize && (n.template = tS(n.template, n.whiteList, n.sanitizeFn)),
                        n
                    );
                }),
                (i._getDelegateConfig = function t() {
                    var e = {};
                    if (this.config) for (var n in this.config) this.constructor.Default[n] !== this.config[n] && (e[n] = this.config[n]);
                    return e;
                }),
                (i._cleanTipClass = function t() {
                    var n = e(this.getTipElement()),
                        i = n.attr("class").match(tb);
                    null !== i && i.length && n.removeClass(i.join(""));
                }),
                (i._handlePopperPlacementChange = function t(e) {
                    var n = e.instance;
                    (this.tip = n.popper), this._cleanTipClass(), this.addAttachmentClass(this._getAttachment(e.placement));
                }),
                (i._fixTransition = function t() {
                    var n = this.getTipElement(),
                        i = this.config.animation;
                    null === n.getAttribute("x-placement") && (e(n).removeClass(tk.FADE), (this.config.animation = !1), this.hide(), this.show(), (this.config.animation = i));
                }),
                (t._jQueryInterface = function n(i) {
                    return this.each(function () {
                        var n = e(this).data(tD);
                        if (!(!n && /dispose|hide/.test(i)) && (n || ((n = new t(this, "object" == typeof i && i)), e(this).data(tD, n)), "string" == typeof i)) {
                            if (void 0 === n[i]) throw TypeError('No method named "' + i + '"');
                            n[i]();
                        }
                    });
                }),
                r(t, null, [
                    {
                        key: "VERSION",
                        get: function t() {
                            return "4.4.1";
                        },
                    },
                    {
                        key: "Default",
                        get: function t() {
                            return tH;
                        },
                    },
                    {
                        key: "NAME",
                        get: function t() {
                            return tC;
                        },
                    },
                    {
                        key: "DATA_KEY",
                        get: function t() {
                            return tD;
                        },
                    },
                    {
                        key: "Event",
                        get: function t() {
                            return t$;
                        },
                    },
                    {
                        key: "EVENT_KEY",
                        get: function t() {
                            return tO;
                        },
                    },
                    {
                        key: "DefaultType",
                        get: function t() {
                            return tw;
                        },
                    },
                ]),
                t
            );
        })();
    (e.fn[tC] = tU._jQueryInterface),
        (e.fn[tC].Constructor = tU),
        (e.fn[tC].noConflict = function () {
            return (e.fn[tC] = ty), tU._jQueryInterface;
        });
    var tF = "popover",
        tM = "bs.popover",
        tj = "." + tM,
        t_ = e.fn[tF],
        tG = "bs-popover",
        tB = RegExp("(^|\\s)" + tG + "\\S+", "g"),
        tK = a({}, tU.Default, { placement: "right", trigger: "click", content: "", template: '<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>' }),
        tx = a({}, tU.DefaultType, { content: "(string|element|function)" }),
        tq = { FADE: "fade", SHOW: "show" },
        tQ = { TITLE: ".popover-header", CONTENT: ".popover-body" },
        t2 = {
            HIDE: "hide" + tj,
            HIDDEN: "hidden" + tj,
            SHOW: "show" + tj,
            SHOWN: "shown" + tj,
            INSERTED: "inserted" + tj,
            CLICK: "click" + tj,
            FOCUSIN: "focusin" + tj,
            FOCUSOUT: "focusout" + tj,
            MOUSEENTER: "mouseenter" + tj,
            MOUSELEAVE: "mouseleave" + tj,
        },
        tY = (function (t) {
            function n() {
                return t.apply(this, arguments) || this;
            }
            (i = n), (s = t), (i.prototype = Object.create(s.prototype)), (i.prototype.constructor = i), (i.__proto__ = s);
            var i,
                s,
                o = n.prototype;
            return (
                (o.isWithContent = function t() {
                    return this.getTitle() || this._getContent();
                }),
                (o.addAttachmentClass = function t(n) {
                    e(this.getTipElement()).addClass(tG + "-" + n);
                }),
                (o.getTipElement = function t() {
                    return (this.tip = this.tip || e(this.config.template)[0]), this.tip;
                }),
                (o.setContent = function t() {
                    var n = e(this.getTipElement());
                    this.setElementContent(n.find(tQ.TITLE), this.getTitle());
                    var i = this._getContent();
                    "function" == typeof i && (i = i.call(this.element)), this.setElementContent(n.find(tQ.CONTENT), i), n.removeClass(tq.FADE + " " + tq.SHOW);
                }),
                (o._getContent = function t() {
                    return this.element.getAttribute("data-content") || this.config.content;
                }),
                (o._cleanTipClass = function t() {
                    var n = e(this.getTipElement()),
                        i = n.attr("class").match(tB);
                    null !== i && i.length > 0 && n.removeClass(i.join(""));
                }),
                (n._jQueryInterface = function t(i) {
                    return this.each(function () {
                        var t = e(this).data(tM);
                        if (!(!t && /dispose|hide/.test(i)) && (t || ((t = new n(this, "object" == typeof i ? i : null)), e(this).data(tM, t)), "string" == typeof i)) {
                            if (void 0 === t[i]) throw TypeError('No method named "' + i + '"');
                            t[i]();
                        }
                    });
                }),
                r(n, null, [
                    {
                        key: "VERSION",
                        get: function t() {
                            return "4.4.1";
                        },
                    },
                    {
                        key: "Default",
                        get: function t() {
                            return tK;
                        },
                    },
                    {
                        key: "NAME",
                        get: function t() {
                            return tF;
                        },
                    },
                    {
                        key: "DATA_KEY",
                        get: function t() {
                            return tM;
                        },
                    },
                    {
                        key: "Event",
                        get: function t() {
                            return t2;
                        },
                    },
                    {
                        key: "EVENT_KEY",
                        get: function t() {
                            return tj;
                        },
                    },
                    {
                        key: "DefaultType",
                        get: function t() {
                            return tx;
                        },
                    },
                ]),
                n
            );
        })(tU);
    (e.fn[tF] = tY._jQueryInterface),
        (e.fn[tF].Constructor = tY),
        (e.fn[tF].noConflict = function () {
            return (e.fn[tF] = t_), tY._jQueryInterface;
        });
    var tX = "scrollspy",
        tz = "bs.scrollspy",
        t0 = "." + tz,
        t1 = e.fn[tX],
        t5 = { offset: 10, method: "auto", target: "" },
        t7 = { offset: "number", method: "string", target: "(string|element)" },
        t3 = { ACTIVATE: "activate" + t0, SCROLL: "scroll" + t0, LOAD_DATA_API: "load" + t0 + ".data-api" },
        t9 = { DROPDOWN_ITEM: "dropdown-item", DROPDOWN_MENU: "dropdown-menu", ACTIVE: "active" },
        tJ = {
            DATA_SPY: '[data-spy="scroll"]',
            ACTIVE: ".active",
            NAV_LIST_GROUP: ".nav, .list-group",
            NAV_LINKS: ".nav-link",
            NAV_ITEMS: ".nav-item",
            LIST_ITEMS: ".list-group-item",
            DROPDOWN: ".dropdown",
            DROPDOWN_ITEMS: ".dropdown-item",
            DROPDOWN_TOGGLE: ".dropdown-toggle",
        },
        tZ = { OFFSET: "offset", POSITION: "position" },
        t6 = (function () {
            function t(t, n) {
                var i = this;
                (this._element = t),
                    (this._scrollElement = "BODY" === t.tagName ? window : t),
                    (this._config = this._getConfig(n)),
                    (this._selector = this._config.target + " " + tJ.NAV_LINKS + "," + (this._config.target + " ") + tJ.LIST_ITEMS + "," + this._config.target + " " + tJ.DROPDOWN_ITEMS),
                    (this._offsets = []),
                    (this._targets = []),
                    (this._activeTarget = null),
                    (this._scrollHeight = 0),
                    e(this._scrollElement).on(t3.SCROLL, function (t) {
                        return i._process(t);
                    }),
                    this.refresh(),
                    this._process();
            }
            var n = t.prototype;
            return (
                (n.refresh = function t() {
                    var n = this,
                        i = this._scrollElement === this._scrollElement.window ? tZ.OFFSET : tZ.POSITION,
                        r = "auto" === this._config.method ? i : this._config.method,
                        s = r === tZ.POSITION ? this._getScrollTop() : 0;
                    (this._offsets = []),
                        (this._targets = []),
                        (this._scrollHeight = this._getScrollHeight()),
                        [].slice
                            .call(document.querySelectorAll(this._selector))
                            .map(function (t) {
                                var n,
                                    i = h.getSelectorFromElement(t);
                                if ((i && (n = document.querySelector(i)), n)) {
                                    var o = n.getBoundingClientRect();
                                    if (o.width || o.height) return [e(n)[r]().top + s, i];
                                }
                                return null;
                            })
                            .filter(function (t) {
                                return t;
                            })
                            .sort(function (t, e) {
                                return t[0] - e[0];
                            })
                            .forEach(function (t) {
                                n._offsets.push(t[0]), n._targets.push(t[1]);
                            });
                }),
                (n.dispose = function t() {
                    e.removeData(this._element, tz),
                        e(this._scrollElement).off(t0),
                        (this._element = null),
                        (this._scrollElement = null),
                        (this._config = null),
                        (this._selector = null),
                        (this._offsets = null),
                        (this._targets = null),
                        (this._activeTarget = null),
                        (this._scrollHeight = null);
                }),
                (n._getConfig = function t(n) {
                    if ("string" != typeof (n = a({}, t5, {}, "object" == typeof n && n ? n : {})).target) {
                        var i = e(n.target).attr("id");
                        i || ((i = h.getUID(tX)), e(n.target).attr("id", i)), (n.target = "#" + i);
                    }
                    return h.typeCheckConfig(tX, n, t7), n;
                }),
                (n._getScrollTop = function t() {
                    return this._scrollElement === window ? this._scrollElement.pageYOffset : this._scrollElement.scrollTop;
                }),
                (n._getScrollHeight = function t() {
                    return this._scrollElement.scrollHeight || Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
                }),
                (n._getOffsetHeight = function t() {
                    return this._scrollElement === window ? window.innerHeight : this._scrollElement.getBoundingClientRect().height;
                }),
                (n._process = function t() {
                    var e = this._getScrollTop() + this._config.offset,
                        n = this._getScrollHeight(),
                        i = this._config.offset + n - this._getOffsetHeight();
                    if ((this._scrollHeight !== n && this.refresh(), e >= i)) {
                        var r = this._targets[this._targets.length - 1];
                        this._activeTarget !== r && this._activate(r);
                        return;
                    }
                    if (this._activeTarget && e < this._offsets[0] && this._offsets[0] > 0) {
                        (this._activeTarget = null), this._clear();
                        return;
                    }
                    for (var s = this._offsets.length, o = s; o--; ) this._activeTarget !== this._targets[o] && e >= this._offsets[o] && (void 0 === this._offsets[o + 1] || e < this._offsets[o + 1]) && this._activate(this._targets[o]);
                }),
                (n._activate = function t(n) {
                    (this._activeTarget = n), this._clear();
                    var i = this._selector.split(",").map(function (t) {
                            return t + '[data-target="' + n + '"],' + t + '[href="' + n + '"]';
                        }),
                        r = e([].slice.call(document.querySelectorAll(i.join(","))));
                    r.hasClass(t9.DROPDOWN_ITEM)
                        ? (r.closest(tJ.DROPDOWN).find(tJ.DROPDOWN_TOGGLE).addClass(t9.ACTIVE), r.addClass(t9.ACTIVE))
                        : (r.addClass(t9.ACTIVE),
                          r
                              .parents(tJ.NAV_LIST_GROUP)
                              .prev(tJ.NAV_LINKS + ", " + tJ.LIST_ITEMS)
                              .addClass(t9.ACTIVE),
                          r.parents(tJ.NAV_LIST_GROUP).prev(tJ.NAV_ITEMS).children(tJ.NAV_LINKS).addClass(t9.ACTIVE)),
                        e(this._scrollElement).trigger(t3.ACTIVATE, { relatedTarget: n });
                }),
                (n._clear = function t() {
                    [].slice
                        .call(document.querySelectorAll(this._selector))
                        .filter(function (t) {
                            return t.classList.contains(t9.ACTIVE);
                        })
                        .forEach(function (t) {
                            return t.classList.remove(t9.ACTIVE);
                        });
                }),
                (t._jQueryInterface = function n(i) {
                    return this.each(function () {
                        var n = e(this).data(tz);
                        if ((n || ((n = new t(this, "object" == typeof i && i)), e(this).data(tz, n)), "string" == typeof i)) {
                            if (void 0 === n[i]) throw TypeError('No method named "' + i + '"');
                            n[i]();
                        }
                    });
                }),
                r(t, null, [
                    {
                        key: "VERSION",
                        get: function t() {
                            return "4.4.1";
                        },
                    },
                    {
                        key: "Default",
                        get: function t() {
                            return t5;
                        },
                    },
                ]),
                t
            );
        })();
    e(window).on(t3.LOAD_DATA_API, function () {
        for (var t = [].slice.call(document.querySelectorAll(tJ.DATA_SPY)), n = t.length, i = n; i--; ) {
            var r = e(t[i]);
            t6._jQueryInterface.call(r, r.data());
        }
    }),
        (e.fn[tX] = t6._jQueryInterface),
        (e.fn[tX].Constructor = t6),
        (e.fn[tX].noConflict = function () {
            return (e.fn[tX] = t1), t6._jQueryInterface;
        });
    var t4 = "bs.tab",
        et = "." + t4,
        ee = e.fn.tab,
        en = { HIDE: "hide" + et, HIDDEN: "hidden" + et, SHOW: "show" + et, SHOWN: "shown" + et, CLICK_DATA_API: "click" + et + ".data-api" },
        ei = { DROPDOWN_MENU: "dropdown-menu", ACTIVE: "active", DISABLED: "disabled", FADE: "fade", SHOW: "show" },
        er = {
            DROPDOWN: ".dropdown",
            NAV_LIST_GROUP: ".nav, .list-group",
            ACTIVE: ".active",
            ACTIVE_UL: "> li > .active",
            DATA_TOGGLE: '[data-toggle="tab"], [data-toggle="pill"], [data-toggle="list"]',
            DROPDOWN_TOGGLE: ".dropdown-toggle",
            DROPDOWN_ACTIVE_CHILD: "> .dropdown-menu .active",
        },
        es = (function () {
            function t(t) {
                this._element = t;
            }
            var n = t.prototype;
            return (
                (n.show = function t() {
                    var n,
                        i,
                        r = this;
                    if (!((this._element.parentNode && this._element.parentNode.nodeType === Node.ELEMENT_NODE && e(this._element).hasClass(ei.ACTIVE)) || e(this._element).hasClass(ei.DISABLED))) {
                        var s = e(this._element).closest(er.NAV_LIST_GROUP)[0],
                            o = h.getSelectorFromElement(this._element);
                        if (s) {
                            var a = "UL" === s.nodeName || "OL" === s.nodeName ? er.ACTIVE_UL : er.ACTIVE;
                            i = (i = e.makeArray(e(s).find(a)))[i.length - 1];
                        }
                        var l = e.Event(en.HIDE, { relatedTarget: this._element }),
                            c = e.Event(en.SHOW, { relatedTarget: i });
                        if ((i && e(i).trigger(l), e(this._element).trigger(c), !(c.isDefaultPrevented() || l.isDefaultPrevented()))) {
                            o && (n = document.querySelector(o)), this._activate(this._element, s);
                            var u = function t() {
                                var n = e.Event(en.HIDDEN, { relatedTarget: r._element }),
                                    s = e.Event(en.SHOWN, { relatedTarget: i });
                                e(i).trigger(n), e(r._element).trigger(s);
                            };
                            n ? this._activate(n, n.parentNode, u) : u();
                        }
                    }
                }),
                (n.dispose = function t() {
                    e.removeData(this._element, t4), (this._element = null);
                }),
                (n._activate = function t(n, i, r) {
                    var s = this,
                        o = (i && ("UL" === i.nodeName || "OL" === i.nodeName) ? e(i).find(er.ACTIVE_UL) : e(i).children(er.ACTIVE))[0],
                        a = r && o && e(o).hasClass(ei.FADE),
                        l = function t() {
                            return s._transitionComplete(n, o, r);
                        };
                    if (o && a) {
                        var c = h.getTransitionDurationFromElement(o);
                        e(o).removeClass(ei.SHOW).one(h.TRANSITION_END, l).emulateTransitionEnd(c);
                    } else l();
                }),
                (n._transitionComplete = function t(n, i, r) {
                    if (i) {
                        e(i).removeClass(ei.ACTIVE);
                        var s = e(i.parentNode).find(er.DROPDOWN_ACTIVE_CHILD)[0];
                        s && e(s).removeClass(ei.ACTIVE), "tab" === i.getAttribute("role") && i.setAttribute("aria-selected", !1);
                    }
                    if (
                        (e(n).addClass(ei.ACTIVE),
                        "tab" === n.getAttribute("role") && n.setAttribute("aria-selected", !0),
                        h.reflow(n),
                        n.classList.contains(ei.FADE) && n.classList.add(ei.SHOW),
                        n.parentNode && e(n.parentNode).hasClass(ei.DROPDOWN_MENU))
                    ) {
                        var o = e(n).closest(er.DROPDOWN)[0];
                        o && e([].slice.call(o.querySelectorAll(er.DROPDOWN_TOGGLE))).addClass(ei.ACTIVE), n.setAttribute("aria-expanded", !0);
                    }
                    r && r();
                }),
                (t._jQueryInterface = function n(i) {
                    return this.each(function () {
                        var n = e(this),
                            r = n.data(t4);
                        if ((r || ((r = new t(this)), n.data(t4, r)), "string" == typeof i)) {
                            if (void 0 === r[i]) throw TypeError('No method named "' + i + '"');
                            r[i]();
                        }
                    });
                }),
                r(t, null, [
                    {
                        key: "VERSION",
                        get: function t() {
                            return "4.4.1";
                        },
                    },
                ]),
                t
            );
        })();
    e(document).on(en.CLICK_DATA_API, er.DATA_TOGGLE, function (t) {
        t.preventDefault(), es._jQueryInterface.call(e(this), "show");
    }),
        (e.fn.tab = es._jQueryInterface),
        (e.fn.tab.Constructor = es),
        (e.fn.tab.noConflict = function () {
            return (e.fn.tab = ee), es._jQueryInterface;
        });
    var eo = "toast",
        ea = "bs.toast",
        el = "." + ea,
        ec = e.fn[eo],
        eh = { CLICK_DISMISS: "click.dismiss" + el, HIDE: "hide" + el, HIDDEN: "hidden" + el, SHOW: "show" + el, SHOWN: "shown" + el },
        eu = { FADE: "fade", HIDE: "hide", SHOW: "show", SHOWING: "showing" },
        ef = { animation: "boolean", autohide: "boolean", delay: "number" },
        e8 = { animation: !0, autohide: !0, delay: 500 },
        ed = { DATA_DISMISS: '[data-dismiss="toast"]' },
        eg = (function () {
            function t(t, e) {
                (this._element = t), (this._config = this._getConfig(e)), (this._timeout = null), this._setListeners();
            }
            var n = t.prototype;
            return (
                (n.show = function t() {
                    var n = this,
                        i = e.Event(eh.SHOW);
                    if ((e(this._element).trigger(i), !i.isDefaultPrevented())) {
                        this._config.animation && this._element.classList.add(eu.FADE);
                        var r = function t() {
                            n._element.classList.remove(eu.SHOWING),
                                n._element.classList.add(eu.SHOW),
                                e(n._element).trigger(eh.SHOWN),
                                n._config.autohide &&
                                    (n._timeout = setTimeout(function () {
                                        n.hide();
                                    }, n._config.delay));
                        };
                        if ((this._element.classList.remove(eu.HIDE), h.reflow(this._element), this._element.classList.add(eu.SHOWING), this._config.animation)) {
                            var s = h.getTransitionDurationFromElement(this._element);
                            e(this._element).one(h.TRANSITION_END, r).emulateTransitionEnd(s);
                        } else r();
                    }
                }),
                (n.hide = function t() {
                    if (this._element.classList.contains(eu.SHOW)) {
                        var n = e.Event(eh.HIDE);
                        e(this._element).trigger(n), !n.isDefaultPrevented() && this._close();
                    }
                }),
                (n.dispose = function t() {
                    clearTimeout(this._timeout),
                        (this._timeout = null),
                        this._element.classList.contains(eu.SHOW) && this._element.classList.remove(eu.SHOW),
                        e(this._element).off(eh.CLICK_DISMISS),
                        e.removeData(this._element, ea),
                        (this._element = null),
                        (this._config = null);
                }),
                (n._getConfig = function t(n) {
                    return (n = a({}, e8, {}, e(this._element).data(), {}, "object" == typeof n && n ? n : {})), h.typeCheckConfig(eo, n, this.constructor.DefaultType), n;
                }),
                (n._setListeners = function t() {
                    var n = this;
                    e(this._element).on(eh.CLICK_DISMISS, ed.DATA_DISMISS, function () {
                        return n.hide();
                    });
                }),
                (n._close = function t() {
                    var n = this,
                        i = function t() {
                            n._element.classList.add(eu.HIDE), e(n._element).trigger(eh.HIDDEN);
                        };
                    if ((this._element.classList.remove(eu.SHOW), this._config.animation)) {
                        var r = h.getTransitionDurationFromElement(this._element);
                        e(this._element).one(h.TRANSITION_END, i).emulateTransitionEnd(r);
                    } else i();
                }),
                (t._jQueryInterface = function n(i) {
                    return this.each(function () {
                        var n = e(this),
                            r = n.data(ea);
                        if ((r || ((r = new t(this, "object" == typeof i && i)), n.data(ea, r)), "string" == typeof i)) {
                            if (void 0 === r[i]) throw TypeError('No method named "' + i + '"');
                            r[i](this);
                        }
                    });
                }),
                r(t, null, [
                    {
                        key: "VERSION",
                        get: function t() {
                            return "4.4.1";
                        },
                    },
                    {
                        key: "DefaultType",
                        get: function t() {
                            return ef;
                        },
                    },
                    {
                        key: "Default",
                        get: function t() {
                            return e8;
                        },
                    },
                ]),
                t
            );
        })();
    (e.fn[eo] = eg._jQueryInterface),
        (e.fn[eo].Constructor = eg),
        (e.fn[eo].noConflict = function () {
            return (e.fn[eo] = ec), eg._jQueryInterface;
        }),
        (t.Alert = E),
        (t.Button = y),
        (t.Carousel = F),
        (t.Collapse = X),
        (t.Dropdown = tc),
        (t.Modal = tv),
        (t.Popover = tY),
        (t.Scrollspy = t6),
        (t.Tab = es),
        (t.Toast = eg),
        (t.Tooltip = tU),
        (t.Util = h),
        Object.defineProperty(t, "__esModule", { value: !0 });
});
