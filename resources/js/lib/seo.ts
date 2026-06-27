export function safeJsonLd(obj: Record<string, unknown>): string {
  return JSON.stringify(obj, (key, value) => {
    if (typeof value === 'string') {
      return value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }
    return value;
  });
}

export function buildProductJsonLd(product: {
  name: string;
  description?: string;
  price: string | number;
  image?: string | null;
  id: number;
  category?: { name: string } | null;
}): Record<string, unknown> {
  return {
    '@context': 'https://schema.org',
    '@type': 'Product',
    name: product.name,
    description: product.description ?? product.name,
    image: product.image ? `${window.location.origin}/storage/${product.image}` : undefined,
    offers: {
      '@type': 'Offer',
      price: Number(product.price),
      priceCurrency: 'SYP',
      availability: 'https://schema.org/InStock',
      url: `${window.location.origin}/products/${product.id}`,
    },
    ...(product.category?.name ? { category: product.category.name } : {}),
  };
}

export function buildBreadcrumbJsonLd(items: { name: string; url: string }[]): Record<string, unknown> {
  return {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: items.map((item, i) => ({
      '@type': 'ListItem',
      position: i + 1,
      name: item.name,
      item: item.url,
    })),
  };
}

export function buildWebSiteJsonLd(): Record<string, unknown> {
  return {
    '@context': 'https://schema.org',
    '@type': 'WebSite',
    name: 'CoreS',
    url: window.location.origin,
    potentialAction: {
      '@type': 'SearchAction',
      target: {
        '@type': 'EntryPoint',
        urlTemplate: `${window.location.origin}/browse?q={search_term_string}`,
      },
      'query-input': 'required name=search_term_string',
    },
  };
}

export function buildStoreJsonLd(): Record<string, unknown> {
  return {
    '@context': 'https://schema.org',
    '@type': 'Store',
    name: 'CoreS',
    description: 'متجر رقمي متخصص في حسابات الألعاب، اشتراكات المنصات، بطاقات الشحن، ورصيد المحافظ الإلكترونية.',
    url: window.location.origin,
    currencyAccepted: 'SYP',
    address: {
      '@type': 'PostalAddress',
      addressCountry: 'SY',
    },
  };
}

export const defaultDescription = 'متجر CoreS — متجر رقمي متخصص في حسابات الألعاب، اشتراكات المنصات، بطاقات الشحن، ورصيد المحافظ الإلكترونية. توصيل فوري وأسعار تنافسية.';
export const siteName = 'CoreS';
