Technical Specification: "The Forge" User Ecosystem

Status: Implemented

1. URL Structure & SEO Strategy

Every user will have a public, search-engine-optimized profile page to establish their digital presence.
    Structure: vilagmuhely.hu/forge/{username}
    Dynamic Meta Tags: The page title should be immersive: "{Username}'s Forge – [Color] Crystal Master | Világműhely". This helps indexing individual community members in Google search results.

2. The 3D Crystal Integration (The Heart of the Profile)
    Visual Display: The top third of the profile features the interactive 3D Crystal (rendered via Three.js or a similar library). It should rotate and react to mouse-over effects.
    RPG-Style Statistics: Next to the crystal, display the raw metrics using thematic labels:
        Rank/Level: Based on Complexity (Number of facets).
        Aura/Resonance: Based on Brightness (Glow %).
        Essence: Based on Clarity (Purity %).

3. The Creator’s Portfolio (The Gallery)
The profile must act as a sales engine by highlighting the user's taste and contributions:
    Authored Works: If the user is a partner creator or affiliate, their products are listed here.
    The Vault (Collection): A showcase of their favorite purchased modules. This acts as social proof—when others see what a "Master" is playing, they are more likely to buy it.
    Echoes (Reviews): A dedicated feed for all reviews written by the user. This rewards active critics and builds authority within the community.

4. The Forge Log (Activity Feed)
A gamified timeline showing recent milestones:
    "[User] just unlocked a new Crystal Facet by reviewing 'Shadows of Ynev'."
    "[User]'s Crystal has shifted to an Emerald hue (Nature/Druidic category focus)."
    "A new treasure has been added to the Vault."

📈 Business Logic: How This Drives Growth
    Retention & Gamification: Users will actively compete to "polish" their crystals. Knowing that a review increases Purity or a purchase increases Complexity turns the shopping experience into a meta-game.
    Conversion & Social Proof: Seeing a high-level "Crystal Master" (50 facets, 1.00 Brightness) recommend a module creates instant trust. It’s the difference between an anonymous review and an "expert" endorsement.
    User-Generated Content (UGC): By rewarding activity with visual growth, you encourage a steady stream of reviews and ratings, which is gold for SEO and product visibility.

Strategic Advice for Development:
Phase 1: The MVP (Minimum Viable Product) Focus on the static profile page and the 3D visualization. Pull the data directly from your existing Laravel backend (calculated Complexity, Glow, and Purity based on current user database actions).
Phase 2: Social Connectivity Implement a "Follow" system where users can get notified when their favorite "Crystal Masters" release new content or post a review. This turns the webshop into a social network for RPG enthusiasts.
