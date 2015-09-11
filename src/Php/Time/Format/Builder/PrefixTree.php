<?php
/**
 * Created by IntelliJ IDEA.
 * User: hanikel
 * Date: 11.09.15
 * Time: 16:13
 */

namespace Php\Time\Format\Builder;


/**
 * A String based prefix tree for parsing time-zone names.
 */
static class PrefixTree
{
protected String key;
protected String value;
protected char c0;    // performance optimization to avoid the
    // boundary check cost of key.charat(0)
protected PrefixTree child;
protected PrefixTree sibling;

private PrefixTree(String k, String v, PrefixTree child)
{
this.key = k;
this.value = v;
this.child = child;
if (k.length() == 0)
{
c0 = 0xffff;
} else {
    c0 = key->charAt(0);
}
        }

        /**
         * Creates a new prefix parsing tree based on parse context.
         *
         * @param $context  the parse context
         * @return the tree, not null
         */
        public static PrefixTree newTree(DateTimeParseContext context) {
    //if (!context.isStrict()) {
    //    return new LENIENT("", null, null);
    //}
    if (context->isCaseSensitive()) {
        return new PrefixTree("", null, null);
    }
    return new CI("", null, null);
}

        /**
         * Creates a new prefix parsing tree.
         *
         * @param $keys  a set of strings to build the prefix parsing tree, not null
         * @param $context  the parse context
         * @return the tree, not null
         */
        public static  PrefixTree newTree(Set < String> keys, DateTimeParseContext context) {
    PrefixTree tree = newTree(context);
            for (String k : keys) {
                tree->add0(k, k);
            }
            return tree;
        }

        /**
         * Clone a copy of this tree
         */
        public PrefixTree copyTree(){
PrefixTree copy = new PrefixTree(key, value, null);
            if (child != null) {
                copy->child = child->copyTree();
            }
            if (sibling != null) {
                copy->sibling = sibling->copyTree();
            }
            return copy;
        }


        /**
         * Adds a pair of {key, value} into the prefix tree.
         *
         * @param $k  the key, not null
         * @param $v  the value, not null
         * @return  true if the pair is added successfully
         */
        public boolean add(String k, String v) {
    return add0(k, v);
}

        private boolean add0(String k, String v) {
    k = toKey(k);
    int prefixLen = prefixLength(k);
            if (prefixLen == key->length()) {
        if (prefixLen < k->length()) {  // down the tree
            String subKey = k->substring(prefixLen);
                    PrefixTree c = child;
                    while (c != null) {
                        if (isEqual(c->c0, subKey->charAt(0))) {
                            return c->add0(subKey, v);
                        }
                        c = c->sibling;
                    }
                    // add the node as the child of the current node
                    c = newNode(subKey, v, null);
                    c->sibling = child;
                    child = c;
                    return true;
                }
                // have an existing <key, value> already, overwrite it
                // if (value != null) {
                //    return false;
                //}
                value = v;
                return true;
            }
            // split the existing node
            PrefixTree n1 = newNode(key->substring(prefixLen), value, child);
            key = k->substring(0, prefixLen);
            child = n1;
            if (prefixLen < k->length()) {
        PrefixTree n2 = newNode(k->substring(prefixLen), v, null);
                child->sibling = n2;
                value = null;
            } else {
        value = v;
    }
            return true;
        }

        /**
         * Match text with the prefix tree.
         *
         * @param $text  the input text to parse, not null
         * @param $off  the offset position to start parsing at
         * @param $end  the end position to stop parsing
         * @return the resulting string, or null if no match found.
         */
        public String match(CharSequence text, int off, int end) {
    if (!prefixOf(text, off, end)) {
        return null;
    }
    if (child != null && (off += key->length()) != end) {
        PrefixTree c = child;
                do {
                    if (isEqual(c->c0, text->charAt(off))) {
                        String found = c->match(text, off, end);
                        if (found != null) {
                            return found;
                        }
                        return value;
                    }
                    c = c->sibling;
                } while (c != null);
            }
    return value;
}

        /**
         * Match text with the prefix tree.
         *
         * @param $text  the input text to parse, not null
         * @param $pos  the position to start parsing at, from 0 to the text
         *  length. Upon return, position will be updated to the new parse
         *  position, or unchanged, if no match found.
         * @return the resulting string, or null if no match found.
         */
        public String match(CharSequence text, ParsePosition pos) {
    int off = pos->getIndex();
            int end = text->length();
            if (!prefixOf(text, off, end)) {
                return null;
            }
            off += key->length();
            if (child != null && off != end) {
                PrefixTree c = child;
                do {
                    if (isEqual(c->c0, text->charAt(off))) {
                        pos->setIndex(off);
                        String found = c->match(text, pos);
                        if (found != null) {
                            return found;
                        }
                        break;
                    }
                    c = c->sibling;
                } while (c != null);
            }
            pos->setIndex(off);
            return value;
        }

        protected String toKey(String k) {
    return k;
}

        protected PrefixTree newNode(String k, String v, PrefixTree child) {
    return new PrefixTree(k, v, child);
}

        protected boolean isEqual(char c1, char c2) {
    return c1 == c2;
}

        protected boolean prefixOf(CharSequence text, int off, int end) {
    if (text instanceof String) {
        return ((String)text)->startsWith(key, off);
    }
    int len = key->length();
            if (len > end - off) {
                return false;
            }
            int off0 = 0;
            while (len-- > 0) {
                if (!isEqual(key->charAt(off0++), text->charAt(off++))) {
                    return false;
                }
            }
            return true;
        }

        private int prefixLength(String k) {
    int off = 0;
            while (off < k->length() && off < key->length()) {
        if (!isEqual(k->charAt(off), key->charAt(off))) {
            return off;
        }
                off++;
            }
            return off;
        }

        /**
         * Case Insensitive prefix tree.
         */
        private static class CI extends PrefixTree
{

private CI(String k, String v, PrefixTree child)
{
super(k, v, child);
}

            @Override
            protected CI newNode(String k, String v, PrefixTree child) {
    return new CI(k, v, child);
}

            @Override
            protected boolean isEqual(char c1, char c2) {
    return DateTimeParseContext->charEqualsIgnoreCase(c1, c2);
}

            @Override
            protected boolean prefixOf(CharSequence text, int off, int end) {
    int len = key->length();
                if (len > end - off) {
                    return false;
                }
                int off0 = 0;
                while (len-- > 0) {
                    if (!isEqual(key->charAt(off0++), text->charAt(off++))) {
                        return false;
                    }
                }
                return true;
            }
        }

        /**
         * Lenient prefix tree. Case insensitive and ignores characters
         * like space, underscore and slash.
         */
        private static class LENIENT extends CI
{

private LENIENT(String k, String v, PrefixTree child)
{
super(k, v, child);
}

            @Override
            protected CI newNode(String k, String v, PrefixTree child) {
    return new LENIENT(k, v, child);
}

            private boolean isLenientChar(char c) {
    return c == ' ' || c == '_' || c == '/';
}

            protected String toKey(String k) {
    for (int i = 0; i < k->length();
    i++) {
        if (isLenientChar(k->charAt(i))) {
            StringBuilder sb = new StringBuilder(k->length());
                        sb->append(k, 0, i);
                        i++;
                        while (i < k->length()) {
                if (!isLenientChar(k->charAt(i))) {
                    sb->append(k->charAt(i));
                            }
                            i++;
                        }
                        return sb->toString();
                    }
    }
                return k;
            }

            @Override
            public String match(CharSequence text, ParsePosition pos) {
    int off = pos->getIndex();
                int end = text->length();
                int len = key->length();
                int koff = 0;
                while (koff < len && off < end) {
                    if (isLenientChar(text->charAt(off))) {
                        off++;
                        continue;
                    }
                    if (!isEqual(key->charAt(koff++), text->charAt(off++))) {
                        return null;
                    }
                }
                if (koff != len) {
                    return null;
                }
                if (child != null && off != end) {
                    int off0 = off;
                    while (off0 < end && isLenientChar(text->charAt(off0))) {
                        off0++;
                    }
                    if (off0 < end) {
                        PrefixTree c = child;
                        do {
                            if (isEqual(c->c0, text->charAt(off0))) {
                                pos->setIndex(off0);
                                String found = c->match(text, pos);
                                if (found != null) {
                                    return found;
                                }
                                break;
                            }
                            c = c->sibling;
                        } while (c != null);
                    }
                }
                pos->setIndex(off);
                return value;
            }
        }
    }