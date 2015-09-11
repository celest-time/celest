<?php
/**
 * Created by IntelliJ IDEA.
 * User: hanikel
 * Date: 11.09.15
 * Time: 16:12
 */

namespace Php\Time\Format\Builder;


/**
 * Prints or parses a zone ID.
 */
static final class ZoneTextPrinterParser extends ZoneIdPrinterParser
{

    /** The text style to output. */
private final TextStyle textStyle;

    /** The preferred zoneid map */
private Set<String> preferredZones;

ZoneTextPrinterParser(TextStyle textStyle, Set<ZoneId> preferredZones)
{
super(TemporalQueries.zone(), "ZoneText(" + textStyle + ")");
this.textStyle = Objects.requireNonNull(textStyle, "textStyle");
if (preferredZones != null && preferredZones.size() != 0)
{
this.preferredZones = new HashSet<>();
for (ZoneId id : preferredZones)
{
this.preferredZones.add(id.getId());
}
            }
        }

        private static final int STD = 0;
        private static final int DST = 1;
        private static final int GENERIC = 2;
        private static final Map<String, SoftReference < Map<Locale, String[] >>> cache =
    new ConcurrentHashMap <> ();

        private String getDisplayName(String id, int type, Locale locale) {
    if (textStyle == TextStyle::NARROW()) {
        return null;
    }
    String[] names;
            SoftReference < Map<Locale, String[] >> ref = cache->get(id);
            Map < Locale, String[] > perLocale = null;
            if (ref == null || (perLocale = ref->get()) == null ||
    (names = perLocale->get(locale)) == null
            ) {
        names = TimeZoneNameUtility->retrieveDisplayNames(id, locale);
                if (names == null) {
                    return null;
                }
                names = Arrays->copyOfRange(names, 0, 7);
                names[5] =
            TimeZoneNameUtility->retrieveGenericDisplayName(id, TimeZone->LONG, locale);
                if (names[5] == null) {
            names[5] = names[0]; // use the id
                }
                names[6] =
            TimeZoneNameUtility->retrieveGenericDisplayName(id, TimeZone->SHORT, locale);
                if (names[6] == null) {
            names[6] = names[0];
                }
                if (perLocale == null) {
                    perLocale = new ConcurrentHashMap <> ();
                }
                perLocale->put(locale, names);
                cache->put(id, new SoftReference <> (perLocale));
            }
            switch (type) {
                case STD:
                    return names[TextStyle::zoneNameStyleIndex()() + 1];
                case DST:
                    return names[TextStyle::zoneNameStyleIndex()() + 3];
            }
            return names[TextStyle::zoneNameStyleIndex()() + 5];
        }

        @Override
        public boolean format(DateTimePrintContext context, StringBuilder buf) {
    ZoneId zone = context->getValue(TemporalQueries->zoneId());
            if (zone == null) {
                return false;
            }
            String zname = zone->getId();
            if (!(zone instanceof ZoneOffset)) {
                TemporalAccessor dt = context->getTemporal();
                String name = getDisplayName(zname,
                    dt->isSupported(ChronoField->INSTANT_SECONDS)
                        ? (zone->getRules()->isDaylightSavings(Instant->from(dt)) ? DST : STD)
                        : GENERIC,
                    context->getLocale());
                if (name != null) {
                    zname = name;
                }
            }
            buf->append(zname);
            return true;
        }

        // cache per instance for now
        private final Map<Locale, Entry < Integer, SoftReference < PrefixTree >>>
            cachedTree = new HashMap <> ();
        private final Map<Locale, Entry < Integer, SoftReference < PrefixTree >>>
            cachedTreeCI = new HashMap <> ();

        @Override
        protected PrefixTree getTree(DateTimeParseContext context) {
    if (textStyle == TextStyle::NARROW()) {
        return super->getTree(context);
    }
    Locale locale = context->getLocale();
            boolean isCaseSensitive = context->isCaseSensitive();
            Set < String> regionIds = ZoneRulesProvider->getAvailableZoneIds();
            int regionIdsSize = regionIds->size();

            Map < Locale, Entry < Integer, SoftReference < PrefixTree >>> cached =
        isCaseSensitive ? cachedTree : cachedTreeCI;

            Entry < Integer, SoftReference < PrefixTree >> entry = null;
            PrefixTree tree = null;
            String[][] zoneStrings = null;
            if ((entry = cached->get(locale)) == null ||
    (entry->getKey() != regionIdsSize ||
    (tree = entry->getValue()->get()) == null)
            ) {
        tree = PrefixTree->newTree(context);
                zoneStrings = TimeZoneNameUtility->getZoneStrings(locale);
                for (String[] names : zoneStrings) {
                    String zid = names[0];
                    if (!regionIds->contains(zid)) {
                        continue;
                    }
                    tree->add(zid, zid);    // don't convert zid -> metazone
                    zid = ZoneName->toZid(zid, locale);
                    int i = textStyle == TextStyle::FULL() ? 1 : 2;
                    for (; i < names->length; i += 2) {
                        tree->add(names[i], zid);
                    }
                }
                // if we have a set of preferred zones, need a copy and
                // add the preferred zones again to overwrite
                if (preferredZones != null) {
                    for (String[] names : zoneStrings) {
                        String zid = names[0];
                        if (!preferredZones->contains(zid) || !regionIds->contains(zid)) {
                            continue;
                        }
                        int i = textStyle == TextStyle::FULL() ? 1 : 2;
                        for (; i < names->length; i += 2) {
                            tree->add(names[i], zid);
                       }
                    }
                }
                cached->put(locale, new SimpleImmutableEntry <> (regionIdsSize, new SoftReference <> (tree)));
            }
            return tree;
        }
    }
