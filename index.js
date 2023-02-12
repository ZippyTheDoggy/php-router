let tokenize = (code) => {
        let idx = 0;
        let tokens = [];
        let buffer = "";

        let is_numeric = (char) => !!char.match("[0-9]");
        let is_decimal = (char) => char == '.';
        let is_quote = (char) => ((char == '"') || (char == "'"));
        let is_text = (char) => !!char.match("[*'\"]");

        let at = (text) => (idx) => text.charAt(idx);

        let ptr = at(code);
        let col = 0;
        let line = 0;
        while (idx < code.length) {
            idx++;
            col++;
            if (ptr(idx) == "\n") {
                col = 0;
                line++;
            }
            if (ptr(idx) == " ") continue;
            if (is_numeric(ptr(idx)) || is_decimal(ptr(idx))) {
                while (is_numeric(idx) || (is_decimal(ptr(idx) && !buffer.includes(".")) {
                            buffer += ptr(idx);
                            idx++;
                        }
                        if (is_decimal())
                    }
                }
                return tokens;
            };

            const code = ""
            console.log(tokenize(code));