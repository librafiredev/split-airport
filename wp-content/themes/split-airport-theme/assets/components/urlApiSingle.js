const urlApiSingle = (param, term, deleteParam = false) => {
    const url = new URL(window.location.href);
    url.searchParams.delete("pageNumber");

    if (deleteParam === true) {
        url.searchParams.delete(param);
    } else {
        url.searchParams.set(param, term);
    }

    history.pushState({}, "", url);
};

export default urlApiSingle;
