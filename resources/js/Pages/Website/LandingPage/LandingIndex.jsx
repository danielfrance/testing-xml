import About from "../SiteComponents/About";
import Blog from "../SiteComponents/Blog";
import CTA from "../SiteComponents/CTA";
import Contact from "../SiteComponents/Contact";
import Faq from "../SiteComponents/Faq";
import Features from "../SiteComponents/Features";
import Hero from "../SiteComponents/Hero";
import Pricing from "../SiteComponents/Pricing";
import Testimonials from "../SiteComponents/Testimonials";
import WebLayout from "../WebLayout";

export default function LandingIndex() {
    return (
        <WebLayout>
            <Hero />
            <Features />
            <About />
            <CTA />
            <Pricing />
            {/* Need to fix the slider */}
            {/* <Testimonials /> */}
            <Faq />
            <Blog />
            <Contact />
        </WebLayout>
    );
}