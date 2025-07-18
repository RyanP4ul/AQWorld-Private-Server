function createSignal(initialValue) {
    let value = initialValue;
    const subscribers = new Set();

    function get() {
        return value;
    }

    function set(newValue) {
        if (value !== newValue) {
            value = newValue;
            subscribers.forEach(fn => fn(value));
        }
    }

    function subscribe(fn) {
        subscribers.add(fn);
        fn(value);
        return () => subscribers.delete(fn);
    }

    return [get, set, subscribe];
}