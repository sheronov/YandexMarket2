export function declension(number, titles, withNum = false) {
    let cases = [2, 0, 1, 1, 1, 2];
    let decl = titles[(number % 100 > 4 && number % 100 < 20) ? 2 : cases[(number % 10 < 5) ? number % 10 : 5]];
    if (withNum) {
        return number + ' ' + decl;
    }
    return decl;
}